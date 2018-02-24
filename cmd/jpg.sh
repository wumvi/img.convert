#!/bin/bash
# jpg.sh input.png out.jpg
convert $1 TGA:- 2> /dev/null | cjpeg -quality 95 -targa -outfile $2