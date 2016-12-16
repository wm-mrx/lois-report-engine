<?php
	class Utils{
		
		public static function dateIDFormat($date, $type){
			$date=date_create($date);
			$date=date_format($date,"Y-m-d");
			$hari=array('Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu');
			
			$bulan=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli',
							'08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
							
			$pecah=explode('-',$date);
			$tgl=$pecah[2];
			$bln=$pecah[1];
			$thn=$pecah[0];
			$hr=date('D', strtotime($date));
			
			switch($type){
				case 1:
					return $hari[$hr].', '.$tgl.' '.$bulan[$bln].' '.$thn;
				case 2:
				    return ', '.$tgl.' '.$bulan[$bln].' '.$thn;
				case 3:
					return ' '.$tgl.' '.$bulan[$bln].' '.$thn;
				default:
					return null;
			}
		}
	}
?>