<?php
	require_once 'config.php';
	require_once 'utils.php';
	
	class Delivery extends FPDF {
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
	
		public function buildReport($data){
			$w = array(10, 20, 35, 35, 30, 20, 20, 30, 30, 25, 25);
			
			//Header
			$this->addCell(array_sum($w), 10, 'PT. LIMAS SENTOSA ANTARNUSA', 'Helvetica', 'B', 12);
			$this->Ln(6);
			
			//Report Title
			$this->addCell(array_sum($w), 10, 'LAPORAN SJ TERBAYAR', 'Helvetica', 'U', 14);
			$this->Ln(6);
			
			//Location
			$this->addCell(array_sum($w), 10, $data['location'], 'Helvetica', 'B', 10);
			$this->Ln(6);
			
			//Report date
			if(isset($data['startDate']) && isset($data['endDate'])){
				$this->Ln(4);
				$dateStr = 'Periode '. Utils::dateIDFormat($data['startDate'], 3).' - '. Utils::dateIDFormat($data['endDate'], 3);
				$this->addCell(array_sum($w), 10, $dateStr, 'Helvetica', 'B', 9);
			}
			
			$this->Ln(10);
			$this->addCells('Helvetica', 'B', 9, $w, $data['headers']);
			$this->Ln();
			
			$this->SetFont('Helvetica','',9);
			
			$rowNumber = 1;
			
			foreach($data['rows'] as $row){
				$dataValue = array();
				
				$dataValue[]= array(
					$rowNumber++,
					$row['spbNumber'],
					$row['sender'],
					$row['receiver'],
					$row['content'],
					number_format($row['totalColli']),
					number_format($row['totalWeight']),
					'Rp. '.number_format( $row['price'],2,',','.'),
					$row['paymentMethod'],
					$row['destinationRegion'],
					date('d/m/Y',strtotime($row['transactionDate']))
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
					$this->Cell($w[5],$tHeight,$dataValue[$i][5],$merge,0,'R');
					$this->Cell($w[6],$tHeight,$dataValue[$i][6],$merge,0,'R');
					$this->Cell($w[7],$tHeight,$dataValue[$i][7],$merge,0,'R');			
					$this->Cell($w[8],$tHeight,isset($dataValue[$i][8]) ? $dataValue[$i][8] : ' ',$merge);
					$this->Cell($w[9],$tHeight,$dataValue[$i][9],$merge);
					$this->Cell($w[10],$tHeight,$dataValue[$i][10],$merge);
					
					$this->Ln($tHeight);
			
					if($merge = 'LR')
						$previousMerge = true;
					else
						$previousMerge = false;
				}
			}
		
			$this->SetFont('Helvetica','B',9);
			$this->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4],7,'Total','LRB',0,'C');
			$this->Cell($w[5],7,number_format($data['sumTotalColli']),'LRB',0,'R');
			$this->Cell($w[6],7,number_format($data['sumTotalWeight']),'LRB',0,'R');
			$this->Cell($w[7],7,'Rp. '.number_format($data['sumPrice'],2,',','.'),'LRB',0,'R');
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