REM This will generate the zipfiles for Wissensmatrix in /build/packages
REM This needs the zip binaries from Info-Zip installed. An installer can be found http://gnuwin32.sourceforge.net/packages/zip.htm
setlocal
SET PATH=%PATH%;C:\Program Files (x86)\GnuWin32\bin
rmdir /q /s packages
mkdir packages
REM Component
cd ../com_wissensmatrix/
zip -r ../build/packages/com_wissensmatrix.zip *
REM Modules
cd ../mod_wissensmatrix_legend/
zip -r ../build/packages/mod_wissensmatrix_legend.zip *
cd ../mod_wissensmatrix_userreport/
zip -r ../build/packages/mod_wissensmatrix_userreport.zip *
REM Plugins
REM Package
cd ../build/packages/
copy ..\..\pkg_wissensmatrix.xml
zip pkg_wissensmatrix.zip *
del pkg_wissensmatrix.xml
