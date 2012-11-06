<?php
/**
 * Handles mime types for files
 * 
 * @author paul.visco@roswellpark.org
 * @package Files
 */
namespace sb\Files;

class Mime
{

    public $mime_types = <<<'MIME'
ai application/postscript
aif audio/x-aiff
aifc audio/x-aiff
aiff audio/x-aiff
asc text/plain
atom application/atom+xml
au     audio/basic
avi video/x-msvideo
bcpio application/x-bcpio
bin application/octet-stream
bmp image/bmp
cdf application/x-netcdf
cgm image/cgm
class application/octet-stream
cpio application/x-cpio
cpt application/mac-compactpro
csh application/x-csh
css text/css
dcr application/x-director
dif video/x-dv
dir application/x-director
djv image/vnd.djvu
djvu image/vnd.djvu
dll application/octet-stream
dmg application/octet-stream
dms application/octet-stream
doc application/msword
dtd application/xml-dtd
dv     video/x-dv
dvi application/x-dvi
dxr application/x-director
eps application/postscript
etx text/x-setext
exe application/octet-stream
ez     application/andrew-inset
gif image/gif
gram application/srgs
grxml application/srgs+xml
gtar application/x-gtar
hdf application/x-hdf
hqx application/mac-binhex40
htm text/html
html text/html
ice x-conference/x-cooltalk
ico image/x-icon
ics text/calendar
ief image/ief
ifb text/calendar
jnlp application/x-java-jnlp-file
jpeg image/jpeg
jpg image/jpeg
js application/x-javascript
latex application/x-latex
lha application/octet-stream
lzh application/octet-stream
m3u audio/x-mpegurl
m4a audio/mp4a-latm
m4b audio/mp4a-latm
m4p audio/mp4a-latm
m4u video/vnd.mpegurl
m4v video/x-m4v
mathml application/mathml+xml
mid audio/midi
midi audio/midi
mov video/quicktime
mp3 audio/mpeg
mp4 video/mp4
mpg video/mpeg
mpga audio/mpeg
ogg application/ogg
pbm image/x-portable-bitmap
pct image/pict
pdb chemical/x-pdb
pdf application/pdf
pict image/pict
png image/png
ppm image/x-portable-pixmap
ppt application/vnd.ms-powerpoint
pps application/vnd.ms-powerpoint
ppsx application/vnd.ms-powerpoint
ps application/postscript
qt video/quicktime
ra audio/x-pn-realaudio
ram audio/x-pn-realaudio
rdf application/rdf+xml
rm application/vnd.rn-realmedia
rtf text/rtf
rtx text/richtext
sit application/x-stuffit
so application/octet-stream
svg image/svg+xml
swf application/x-shockwave-flash
tar application/x-tar
tcl application/x-tcl
tif image/tiff
tsv text/tab-separated-values
txt text/plain
vrml model/vrml
vxml application/voicexml+xml
wav audio/x-wav
wbmp image/vnd.wap.wbmp
wbmxl application/vnd.wap.wbxml
wml text/vnd.wap.wml
wmlc application/vnd.wap.wmlc
wmls text/vnd.wap.wmlscript
wmlsc application/vnd.wap.wmlscriptc
wrl model/vrml
xbm image/x-xbitmap
xhtml application/xhtml+xml
xls application/vnd.ms-excel
xml application/xml
xpm image/x-xpixmap
xsl application/xml
xslt application/xslt+xml
xul application/vnd.mozilla.xul+xml
zip application/zip
MIME;
}

