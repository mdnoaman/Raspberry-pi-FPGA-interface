import spidev

spi = spidev.SpiDev()
spi.open(0,0)
#spi.max_speed_hz = 3900000
spi.max_speed_hz = 7800000

# trigger
spi.xfer2([0b01110010])
spi.close()	#end try