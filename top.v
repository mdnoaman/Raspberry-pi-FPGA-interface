module SPI_slave(clk_in, rst, SCK, MOSI, MISO, SSEL, LED, LED_hi);
input clk_in, rst;

input SCK, SSEL, MOSI;
output MISO;

output [7:0] LED;
output LED_hi;

clk_mngr instance_name
	(// Clock in ports
	.CLK_IN1(clk_in),      // IN
	// Clock out ports
	.CLK_OUT1(clk),     // OUT
	// Status and control signals
	.RESET(RESET),// IN
	.LOCKED(LOCKED));      // OUT

reg reset;
//assign reset = 0;
reg  we;
reg  [11:0] addr;
reg  [47:0]  din;
wire [47:0] dout;

bram bram (
  .clka(clk), // input clka
  .rsta(reset), // input rsta
  .wea(we), // input [0 : 0] wea
  .addra(addr), // input [11 : 0] addra
  .dina(din), // input [47 : 0] dina
  .douta(dout) // output [47 : 0] douta
);


// sync SCK to the FPGA clock using a 3-bits shift register
reg [2:0] SCKr;  always @(posedge clk) SCKr <= {SCKr[1:0], SCK};
wire SCK_risingedge = (SCKr[2:1]==2'b01);  // now we can detect SCK rising edges
wire SCK_fallingedge = (SCKr[2:1]==2'b10);  // and falling edges

// same thing for SSEL
reg [2:0] SSELr;  always @(posedge clk) SSELr <= {SSELr[1:0], SSEL};
wire SSEL_active = ~SSELr[1];  // SSEL is active low
wire SSEL_startmessage = (SSELr[2:1]==2'b10);  // message starts at falling edge
wire SSEL_endmessage = (SSELr[2:1]==2'b01);  // message stops at rising edge

// and for MOSI
reg [2:0] MOSIr;  always @(posedge clk) MOSIr <= {MOSIr[1:0], MOSI};
wire MOSI_data = MOSIr[1];

// we handle SPI in 8-bits format, so we need a 3 bits counter to count the bits as they come in
reg [2:0] bitcnt;

reg byte_received;  // high when a byte has been received
reg [7:0] byte_data_received;

always @(posedge clk)
begin
  if(~SSEL_active)
    bitcnt <= 3'b000;
  else
  if(SCK_risingedge)
  begin
    bitcnt <= bitcnt + 3'b001;

    // implement a shift-left register (since we receive the data MSB first)
    byte_data_received <= {byte_data_received[6:0], MOSI_data};
  end
end

always @(posedge clk) byte_received <= SSEL_active && SCK_risingedge && (bitcnt==3'b111);

// we use the LSB of the data received to control an LED
reg [7:0] LEDr;
reg LEDr_hi = 1;

assign LED = ~LEDr;
assign LED_hi = LEDr_hi;


reg [39:0] tim;
reg trig;
reg [30:0] cntr;
reg x;
reg [15:0] seq_len;
reg [15:0] lnum;
reg [7:0] lstr;
reg [7:0] lend;
reg [7:0] llen;
reg [15:0] lcnt;

reg [15:0] lnum2;
reg [7:0] lstr2;
reg [7:0] lend2;
reg [7:0] llen2;
reg [15:0] lcnt2;

reg [7:0] step;  // state machine cases
reg [7:0] ind;

always @ (posedge clk)begin

//  if(trig ==0) 
//  begin  
	if(byte_received == 1)
	begin
	case(step)
	8'd0: begin
				if(byte_data_received == 8'b01110111)  // press w button to enable writing
				begin
					step <= 8'd1; 	// go to next case step
					ind <= 8'd47;
					addr <= 0;
					trig <= 0;
				end
				else if(byte_data_received == 8'b01110010) // software trigger
				begin
					addr <= 0;
					trig <= 1;
					step <= 8'd0;
				end
				else begin
					step <= 8'd0 ;
				end
			end
	8'd1: begin
				seq_len[15:8] <= byte_data_received;  // receive length byte2
				step <= 8'd2;
			end
	8'd2: begin
				seq_len[7:0] <= byte_data_received;   // receive length byte1
				step <= 8'd3;
			end
	8'd3: begin
				lstr[7:0] <= byte_data_received;   // start of the loop byte
				step <= 8'd4;
			end
	8'd4: begin
				lend[7:0] <= byte_data_received;   // end of the loop byte
				step <= 8'd5;
			end
	8'd5: begin
				llen <= lend - lstr;    // calculates the difference between start and end loop
				lnum[15:8] <= byte_data_received;  // receives number of repeatations in the loop
				step <= 8'd6;
			end
	8'd6: begin
				lnum[7:0] <= byte_data_received;   // receives number of repeatations in the loop
				step <= 8'd7;
			end
			
	8'd7: begin
				lstr2[7:0] <= byte_data_received;   // start of the loop byte
				step <= 8'd8;
			end
	8'd8: begin
				lend2[7:0] <= byte_data_received;   // end of the loop byte
				step <= 8'd9;
			end
	8'd9: begin
				llen2 <= lend2 - lstr2;    // calculates the difference between start and end loop
				lnum2[15:8] <= byte_data_received;  // receives number of repeatations in the loop
				step <= 8'd10;
			end
	8'd10: begin
				lnum2[7:0] <= byte_data_received;   // receives number of repeatations in the loop
				step <= 8'd11;
			end


	8'd11: begin
				if(we ==1)
				begin
					we <= 0;
					addr <= addr + 1;
				end
					din[ind -:8]  <= byte_data_received;
					LEDr <= din[47:40];
					ind <= ind - 8'd8;
				if(ind == 8'd7)
				begin
					we <= 1;
					ind <= 8'd47;
				end 
				if (addr == seq_len)
				begin
					 step <= 8'd12;
					 we <= 0;
					 ind <= 8'd0;
				end
			end

	8'd12: begin
					step <= 8'd0;
			end
	endcase
	end

// external trigger  
	if(rst == 0 && x == 0)   // external trigger
	begin
//     we   <= 0;  // disable write
   	  addr <= 0;
	     trig <= 1;
		  x <= 1;  // latch
	end
   
	if(rst == 1 )   // external trigger latch enables it to trigger only once when triggered
	begin
   	  x <= 0;		  
	end
//   end
   

// read out the data which was stored in the bram as 'trig' goes high  
  if(trig == 1 )    // readout data stored in bram
  begin
	LEDr <= dout[47:40];  // data from bram (dout) is stored on the data register which is assigned to output
	cntr <= cntr + 1;   // some discripancy in while storing the data in 0th address
	tim <= dout[39:0];
	if(cntr >= tim)
	begin
		if(addr == lend)  // first loop
		begin
		cntr <= 0;
			if(lcnt < lnum)begin
			addr <= addr - llen;
			lcnt <= lcnt + 1;
			end
			else begin
			addr <= addr + 1;   // bram address increases
			cntr <= 0;
			lcnt <= 8'd0;
			end
		end

		else if(addr == lend2)  // second loop
		begin
		cntr <= 0;
			if(lcnt2 < lnum2)begin
			addr <= addr - llen2;
			lcnt2 <= lcnt2 + 1;
			end
			else begin
			addr <= addr + 1;   // bram address increases
			cntr <= 0;
			lcnt2 <= 8'd0;
			end
		end
		
		else begin
		addr <= addr + 1;   // bram address increases
		cntr <= 0;
		end
	end
	if(addr == seq_len)  // sets limit on the bram address
	begin
		trig <= 0;
	end
  end	

end





reg [7:0] byte_data_sent;
reg [7:0] cnt;
always @(posedge clk) if(SSEL_startmessage) cnt<=cnt+8'h1;  // count the messages

always @(posedge clk)
if(SSEL_active)
begin
  if(SSEL_startmessage)
    byte_data_sent <= cnt;  // first byte sent in a message is the message count
  else
  if(SCK_fallingedge)
  begin
    if(bitcnt==3'b000)
      byte_data_sent <= 8'h00;  // after that, we send 0s
    else
      byte_data_sent <= {byte_data_sent[6:0], 1'b0};
  end
end

assign MISO = byte_data_sent[7];  // send MSB first
// we assume that there is only one slave on the SPI bus
// so we don't bother with a tri-state buffer for MISO
// otherwise we would need to tri-state MISO when SSEL is inactive

endmodule