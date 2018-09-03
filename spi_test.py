import spidev
import time
#import requests

with open("setting.txt","r") as ins:
	array = []
	for line in ins:
		array.append(line)

line4 = array[3]
line4 = line4.split(',')
row = int(line4[0])
col = int(line4[1])
print row,col
		
line1 = array[0]
line1 = line1.split(',')
line1 = map(int, line1[0:-1])
#print line1

line2 = array[1]
line2 = line2.split(',')
line2 = line2[0:-1]
line2 = [int(i,2) for i in line2]
#print line2

line3 = array[2]
line3 = line3.split(',')
line3 = map(int, line3[0:-1])
print "<br>"
print line3


st_tm_arr = []
for ind in range(col):
	tim = bin(line1[ind])
	tim = tim[2:len(tim)].zfill(40)
	tim_arr = []
	for i in range(5):
		tim_arr.append(int(tim[8*i : 8*(i+1)],2))

	st_tm = [line2[ind]] + tim_arr
	st_tm_arr.append(st_tm)
print "<br>"
print st_tm_arr


spi = spidev.SpiDev()
spi.open(0,0)
#spi.max_speed_hz = 3900000
spi.max_speed_hz = 7800000

# handshaking
spi.xfer2([0b01110111])
#spi.xfer2([0b00110011])

#sequence length
seq_len = bin(col)
seq_len = seq_len[2:len(seq_len)].zfill(16)
sq1 = seq_len[0:8]
sq2 = seq_len[8:16]
spi.xfer2([int(sq1,2)])
spi.xfer2([int(sq2,2)])
#print sq1
#print sq2
#print col

#start loop1 
l_str = bin(line3[0]-1)
l_str = l_str[2:len(l_str)].zfill(8)
ls1 = l_str[0:8]
#ls2 = l_str[8:16]
spi.xfer2([int(ls1,2)])
#spi.xfer2([int(ls2,2)])
#print ls1
#print ls2

#end loop1 
l_end = bin(line3[1]-1)
l_end = l_end[2:len(l_end)].zfill(8)
le1 = l_end[0:8]
#le2 = l_end[8:16]
spi.xfer2([int(le1,2)])
#spi.xfer2([int(le2,2)])
#print le1
#print le2

#num loop1 
l_num = bin(line3[2]-1)
l_num = l_num[2:len(l_num)].zfill(16)
ln1 = l_num[0:8]
ln2 = l_num[8:16]
spi.xfer2([int(ln1,2)])
spi.xfer2([int(ln2,2)])
#print ln1
#print ln2


#start loop2
l_str = bin(line3[3]-1)
l_str = l_str[2:len(l_str)].zfill(8)
ls1 = l_str[0:8]
#ls2 = l_str[8:16]
spi.xfer2([int(ls1,2)])
#spi.xfer2([int(ls2,2)])
#print ls1
#print ls2

#end loop2 
l_end = bin(line3[4]-1)
l_end = l_end[2:len(l_end)].zfill(8)
le1 = l_end[0:8]
#le2 = l_end[8:16]
spi.xfer2([int(le1,2)])
#spi.xfer2([int(le2,2)])
#print le1
#print le2

#num loop2 
l_num = bin(line3[5]-1)
l_num = l_num[2:len(l_num)].zfill(16)
ln1 = l_num[0:8]
ln2 = l_num[8:16]
spi.xfer2([int(ln1,2)])
spi.xfer2([int(ln2,2)])
#print ln1
#print ln2
#print line3

for i in range(col):
	for j in range(6):
		resp = spi.xfer2([st_tm_arr[i][j]])
#		print st_tm_arr[i][j]

spi.xfer2([0b01010101])
spi.xfer2([0b01010101])
spi.xfer2([0b01010101])

spi.close()	#end try