.PHONY: favicon

favicon: favicon/gumbo.ico

gumbo-green.png: gumbo-green.svg
	inkscape -e gumbo-green.png -d 300 gumbo-green.svg

favicon/192.png: gumbo-green.png
	convert gumbo-green.png -resize 192x192 favicon/192.png
favicon/256.png: gumbo-green.png
	convert gumbo-green.png -resize 256x256 favicon/256.png
favicon/128.png: gumbo-green.png
	convert gumbo-green.png -resize 128x128 favicon/128.png
favicon/96.png: gumbo-green.png
	convert gumbo-green.png -resize 96x96 favicon/96.png
favicon/64.png: gumbo-green.png
	convert gumbo-green.png -resize 64x64 favicon/64.png
favicon/48.png: gumbo-green.png
	convert gumbo-green.png -resize 48x48 favicon/48.png
favicon/32.png: gumbo-green.png
	convert gumbo-green.png -resize 32x32 favicon/32.png
favicon/16.png: gumbo-green.png
	convert gumbo-green.png -resize 16x16 favicon/16.png
favicon/gumbo.ico: favicon/192.png favicon/256.png favicon/128.png favicon/96.png favicon/64.png favicon/48.png favicon/32.png favicon/16.png
	convert \
		favicon/192.png \
		favicon/256.png \
		favicon/128.png \
		favicon/96.png \
		favicon/64.png \
		favicon/48.png \
		favicon/32.png \
		favicon/16.png \
		favicon/gumbo.ico
