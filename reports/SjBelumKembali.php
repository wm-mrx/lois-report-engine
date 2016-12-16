<?php
	require_once 'config.php';
	require_once 'utils.php';
	
	class SjBelumKembali extends FPDF {
		var $userName;
		
		function __construct($orientation='P', $unit='mm', $size='A4'){
			parent::__construct($orientation, $unit, $size);
		}
		
		function setUserName($userName){
			$this->userName = $userName;
		}
		
		private function addCell($width, $height, $text, $fontFamily, $fontWeight, $fontSize){
			$this->SetFont($fontFamily, $fontWeight, $fontSize);
			$this->Cell($width, $height, $text, 0, 0, 'C');
		}
		
		private function addCells($fontFamily, $fontWeight, $fontSize, $columnSizes, $headers){
			$this->SetFont($fontFamily, $fontWeight, $fontSize);
			
			for($i=0; $i<count($headers); $i++)
				$this->Cell($columnSizes[$i], 7, $headers[$i], 1, 0, 'C');
		}
		
		private function addLeftText($to, $region, $name, $position){
			
		}
		
		public function buildReport($data){
			$w = array(10, 15, 35, 20, 40, 20, 20, 30, 30, 25, 25);
			
			$header = 'Kepada Yth,';
			$right =  'Jakarta, ' . Utils::dateIDFormat(date('Y-m-d'), 3);
			$to = 'PT. LIMAS SENTOSA ANTAR NUSA';
			$region = 'Jakarta';
			$recipient = 'Bpk./Ibu.................................../Bag...................................';
			$respect = 'Dengan hormat,';
			$intro = 'Berikut kami sampaikan surat jalan yang belum kembali tujuan daerah';
			$destination = 'Jakarta, Cirebon, sbb:';
			
			$this->SetXY(10, 30);
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($header);
			$this->Cell($length, 2, $header);
			$this->Ln(6);
			
			$this->SetXY(150, 30);
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($right);
			$this->Cell($length, 2, $right);
			$this->Ln(6);
			
			$this->SetFont('Helvetica','B',11);
			$length = $this->GetStringWidth($to);
			$this->Cell($length, 2, $to);
			$this->Ln(6);
			
			$this->SetFont('Helvetica','B',11);
			$length = $this->GetStringWidth('regional ' . $region);
			$this->Cell($length, 2, 'regional ' . $region);
			$this->Ln(6);
			
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($recipient);
			$this->Cell($length, 2, $recipient);
			$this->Ln(15);
			
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($respect);
			$this->Cell($length, 2, $respect);
			$this->Ln(6);
			
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($intro);
			$this->Cell($length, 2, $intro);
			$this->Ln(6);
			
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($destination);
			$this->Cell($length, 2, $destination);
			$this->Ln(6);
			
			$w = array(10, 30, 30, 30, 30, 30, 30);
			$this->Ln(5);
			$this->addCells('Helvetica', 'B', 9, $w, $data['headers']);
			$this->Ln();
			
			$this->SetFont('Helvetica','',9);
			
			$rowNumber = 1;
			
			foreach($data['rows'] as $row){
				$dataValue = array();
				
				$dataValue[]= array(
					$rowNumber++,
					date('d/m/Y',strtotime($row['transactionDate'])),
					$row['spbNumber'],
					$row['sender'],
					$row['receiver'],
					$row['destination'],
					''
				);
				
				$addRowValue = array();
				$w_count = 0;
				
				foreach($dataValue[0] as $col) {	
					$line = floor((2.187*strlen($col))/$w[$w_count]);			
					array_push($addRowValue,$line);	
					
					if ($line>0){
						$addText = explode(" ",$col);
						$textWidth = floor($w[$w_count]/ 2.187);
						$i_data = 0;
						
						for($x=0;$x<$line+1;$x++) {
							$addTextWidth = '';
							$text='';
							
							for($i=$i_data;$i<count($addText);$i++) {	
								$text = $text.$addText[$i];
								$text_len = strlen($text);
								$i_data = $i;
								
								if ($text_len > $textWidth)
									break;			
								
								$addTextWidth = $text;
								$text = $text.' ';	
							}
							
							$dataValue[$x][$w_count]=$addTextWidth;	
						}
					}
					
					$w_count += 1;
				}
				
				$rowCount = max($addRowValue)+1;
				for($i=1;$i<$rowCount;$i++) {
					for($x=0;$x<$w_count;$x++) {
						if ($addRowValue[$x] == 0)
							$dataValue[$i][$x]='';
					}
				}
				
				$previousMerge = false;
				
				for($i=0;$i<$rowCount;$i++) {
					if ($i < ($rowCount-1)){
						$merge = 'LR';
						$tHeight = 6;
					}
					else{
						$merge = 'LRB';
						if ($previousMerge)
							$tHeight = 4;
						else
							$tHeight = 7;
					}
					
					$this->Cell($w[0],$tHeight, $dataValue[$i][0] ,$merge);
					$this->Cell($w[1],$tHeight,$dataValue[$i][1],$merge);
					$this->Cell($w[2],$tHeight,isset($dataValue[$i][2]) ? $dataValue[$i][2] : ' ',$merge);
					$this->Cell($w[3],$tHeight,isset($dataValue[$i][3]) ? $dataValue[$i][3] : ' ',$merge);
					$this->Cell($w[4],$tHeight,isset($dataValue[$i][4]) ? $dataValue[$i][4] : ' ',$merge);
					$this->Cell($w[5],$tHeight,isset($dataValue[$i][5]) ? $dataValue[$i][5] : ' ',$merge,0,'L');
					$this->Cell($w[6],$tHeight,isset($dataValue[$i][6]) ? $dataValue[$i][6] : ' ',$merge,0,'L');
	
					$this->Ln($tHeight);
			
					if($merge = 'LR')
						$previousMerge = true;
					else
						$previousMerge = false;
				}
			}
			
			$this->Ln(6);
			$this->SetFont('Helvetica','',8);
			$length = $this->GetStringWidth('catatan:');
			$this->Cell($length, 2, 'catatan:');
			
			$footer1 = 'Mohon untuk segera ditindaklanjuti demo proses selanjutnya.';
			$footer2 = 'Demikian pemberitahuannya, atas perhatiannya kami ucapkan terima kasih.';
			$respectFooter = 'Hormat Kami,';
			$company = 'PT LIMAS SENTOSA ANTAR NUSA';
			$admin = 'Administrator';
			
			$this->Ln(20);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($footer2);
			$this->Cell($length, 2, $footer1);
			
			$this->Ln(6);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($footer2);
			$this->Cell($length, 2, $footer2);
			
			$this->Ln(15);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($respectFooter);
			$this->Cell($length, 2, $respectFooter);
			
			$this->Ln(6);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($company);
			$this->Cell($length, 2, $company);
			
			$this->Ln(18);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($admin);
			$this->Cell($length, 2, $admin);
		}
	
		public function footer(){
			if(!empty($this->AliasNbPages)){
				$this->SetY(-15);
				$this->SetFont('Helvetica','',8);
				$this->Cell(0,8,'[Page '.$this->PageNo().'/{nb}]'.' This document was produced on '.date('l, dS F Y').', at '.date('h.i a').', by '. $this->userName . ' and delivered from Limas Operating Integrated System (LOIS).',0,0,'C');
			}
		}
	}
?>