sed -e 's/\s*\([\+0-9a-zA-Z]*\).*/\1/' << EOF | fdisk /dev/mmcblk2
  o     # clear the in-memory partition table
  n     # new partition
  p     # primary partition
  1     # partition number 1
        # default - start at beginning of disk
        # default - extend partition to end of disk
  p     # print the in-memory partition table
  w     # write the partition table
  q     # and we're done
EOF

sed -e 's/\s*\([\+0-9a-zA-Z]*\).*/\1/' << EOF | mkfs.ext3 /dev/mmcblk2
  y
  
EOF

