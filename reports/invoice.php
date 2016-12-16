<?php
	require_once 'config.php';
	require_once 'utils.php';
	
	class Invoice extends FPDF {
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
			$w = array(10, 15, 35, 20, 40, 20, 20, 30, 30, 25, 25);
			
			$invoiceNumber = $data['invoiceNumber'];
			$right =  $data['location'] . Utils::dateIDFormat(date('Y-m-d'), 3);
			
			$header = 'Kepada Yth,';
			$to = $data['recipient'];
			$toLocation = $data['recipientLocation'];
			$respect = 'Dengan hormat,';
			$intro = 'Berikut kami sampaikan perincian tagihan pengiriman barang tujuan';
			$destination = 'Jakarta, Tangerang, sbb:';
			
			$this->SetXY(10, 30);
			$this->SetFont('Helvetica', 'B', 11);
			$length = $this->GetStringWidth($invoiceNumber);
			$this->Cell($length, 2, $invoiceNumber);
			$this->Ln(6);
			
			$this->SetXY(150, 30);
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($right);
			$this->Cell($length, 2, $right);
			$this->Ln(10);
			
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($header);
			$this->Cell($length, 2, $header);
			$this->Ln(6);
			
			$this->SetFont('Helvetica', 'B', 11);
			$length = $this->GetStringWidth($to);
			$this->Cell($length, 2, $to);
			$this->Ln(6);
			
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($toLocation);
			$this->Cell($length, 2, $toLocation);
			$this->Ln(15);
			
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($respect);
			$this->Cell($length, 2, $respect);
			$this->Ln(6);
			
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($intro);
			$this->Cell($length, 2, $intro);
			$this->Ln(6);
			
			$this->SetFont('Helvetica', null, 11);
			$length = $this->GetStringWidth($destination);
			$this->Cell($length, 2, $destination);
			$this->Ln(6);
			
			if($data['typeNum'] == 1)
				$w = array(10, 25, 20, 15, 15, 25, 30, 20, 35);
			else if($data['typeNum'] == 2)
				$w = array(10, 25, 20, 15, 15, 30, 30, 30);
			else
				$w = array(10, 25, 20, 30, 30, 35, 40);
			
			$this->Ln(5);
			$this->addCells('Helvetica', 'B', 9, $w, $data['headers']);
			$this->Ln();
			
			$this->SetFont('Helvetica','',9);
			
			$rowNumber = 1;
			
			foreach($data['rows'] as $row){
				$dataValue = array();
				
				if($data['typeNum'] == 1){
					$dataValue[]= array(
						$rowNumber++,
						date('d/m/Y',strtotime($row['transactionDate'])),
						$row['spbNumber'],
						number_format($row['totalColli']),
						number_format($row['totalWeight']),
						'Rp. '.number_format( $row['workerCost'],2,',','.'),
						'Rp. '.number_format( $row['partnerCost'],2,',','.'),
						'Rp. '.number_format( $row['ppn'],2,',','.'),
						'Rp. '.number_format( $row['price'],2,',','.'),
					);
				}
				else if($data['typeNum'] == 2){
					$dataValue[]= array(
						$rowNumber++,
						date('d/m/Y',strtotime($row['transactionDate'])),
						$row['spbNumber'],
						number_format($row['totalColli']),
						number_format($row['totalWeight']),
						'Rp. '.number_format( $row['workerCost'],2,',','.'),
						'Rp. '.number_format( $row['ppn'],2,',','.'),
						'Rp. '.number_format( $row['price'],2,',','.'),
					);
				}
				
				else{
					$dataValue[]= array(
						$rowNumber++,
						date('d/m/Y',strtotime($row['transactionDate'])),
						$row['spbNumber'],
						number_format($row['totalColli']),
						number_format($row['totalWeight']),
						'Rp. '.number_format( $row['workerCost'],2,',','.'),
						'Rp. '.number_format( $row['partnerCost'],2,',','.')
					);
				}
				
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
					
					if($data['typeNum'] == 1){
						$this->Cell($w[0],$tHeight, $dataValue[$i][0] ,$merge);
						$this->Cell($w[1],$tHeight,$dataValue[$i][1],$merge);
						$this->Cell($w[2],$tHeight,isset($dataValue[$i][2]) ? $dataValue[$i][2] : ' ',$merge);
						$this->Cell($w[3],$tHeight,isset($dataValue[$i][3]) ? $dataValue[$i][3] : ' ',$merge, 0, 'R');
						$this->Cell($w[4],$tHeight,isset($dataValue[$i][4]) ? $dataValue[$i][4] : ' ',$merge, 0, 'R');
						$this->Cell($w[5],$tHeight,isset($dataValue[$i][5]) ? $dataValue[$i][5] : ' ',$merge,0,'R');
						$this->Cell($w[6],$tHeight,isset($dataValue[$i][6]) ? $dataValue[$i][6] : ' ',$merge,0,'R');
						$this->Cell($w[7],$tHeight,isset($dataValue[$i][7]) ? $dataValue[$i][7] : ' ',$merge,0,'R');
						$this->Cell($w[8],$tHeight,isset($dataValue[$i][8]) ? $dataValue[$i][8] : ' ',$merge,0,'R');
					}
					
					else if($data['typeNum'] == 2){
						$this->Cell($w[0],$tHeight, $dataValue[$i][0] ,$merge);
						$this->Cell($w[1],$tHeight,$dataValue[$i][1],$merge);
						$this->Cell($w[2],$tHeight,isset($dataValue[$i][2]) ? $dataValue[$i][2] : ' ',$merge);
						$this->Cell($w[3],$tHeight,isset($dataValue[$i][3]) ? $dataValue[$i][3] : ' ',$merge, 0, 'R');
						$this->Cell($w[4],$tHeight,isset($dataValue[$i][4]) ? $dataValue[$i][4] : ' ',$merge, 0, 'R');
						$this->Cell($w[5],$tHeight,isset($dataValue[$i][5]) ? $dataValue[$i][5] : ' ',$merge,0,'R');
						$this->Cell($w[6],$tHeight,isset($dataValue[$i][6]) ? $dataValue[$i][6] : ' ',$merge,0,'R');
						$this->Cell($w[7],$tHeight,isset($dataValue[$i][7]) ? $dataValue[$i][7] : ' ',$merge,0,'R');
					}
					
					else{
						$this->Cell($w[0],$tHeight, $dataValue[$i][0] ,$merge);
						$this->Cell($w[1],$tHeight,$dataValue[$i][1],$merge);
						$this->Cell($w[2],$tHeight,isset($dataValue[$i][2]) ? $dataValue[$i][2] : ' ',$merge);
						$this->Cell($w[3],$tHeight,isset($dataValue[$i][3]) ? $dataValue[$i][3] : ' ',$merge, 0, 'R');
						$this->Cell($w[4],$tHeight,isset($dataValue[$i][4]) ? $dataValue[$i][4] : ' ',$merge, 0, 'R');
						$this->Cell($w[5],$tHeight,isset($dataValue[$i][5]) ? $dataValue[$i][5] : ' ',$merge,0,'R');
						$this->Cell($w[6],$tHeight,isset($dataValue[$i][6]) ? $dataValue[$i][6] : ' ',$merge,0,'R');
					}
	
					$this->Ln($tHeight);
			
					if($merge = 'LR')
						$previousMerge = true;
					else
						$previousMerge = false;
				}
			}
			
			$this->SetFont('Helvetica','B',9);
			
			if($data['typeNum'] == 1){
				$this->Cell($w[0]+$w[1]+$w[2],7,'Jumlah','LRB',0,'C');
				$this->Cell($w[3],7,number_format($data['sumTotalColli']),'LRB',0,'R');
				$this->Cell($w[4],7,number_format($data['sumTotalWeight']),'LRB',0,'R');
				$this->Cell($w[5],7,'Rp. '.number_format($data['sumWorkerCost'],2,',','.'),'LRB',0,'R');
				$this->Cell($w[6],7,'Rp. '.number_format($data['sumPartnerCost'],2,',','.'),'LRB',0,'R');
				$this->Cell($w[7],7,'Rp. '.number_format($data['sumPpn'],2,',','.'),'LRB',0,'R');
				$this->Cell($w[8],7,'Rp. '.number_format($data['sumPrice'],2,',','.'),'LRB',0,'R');
			}
			
			else if($data['typeNum'] == 2){
				$this->Cell($w[0]+$w[1]+$w[2],7,'Jumlah','LRB',0,'C');
				$this->Cell($w[3],7,number_format($data['sumTotalColli']),'LRB',0,'R');
				$this->Cell($w[4],7,number_format($data['sumTotalWeight']),'LRB',0,'R');
				$this->Cell($w[5],7,'Rp. '.number_format($data['sumWorkerCost'],2,',','.'),'LRB',0,'R');
				$this->Cell($w[6],7,'Rp. '.number_format($data['sumPpn'],2,',','.'),'LRB',0,'R');
				$this->Cell($w[7],7,'Rp. '.number_format($data['sumPrice'],2,',','.'),'LRB',0,'R');
			}
			
			else {
				$this->Cell($w[0]+$w[1]+$w[2],7,'Jumlah','LRB',0,'C');
				$this->Cell($w[3],7,number_format($data['sumTotalColli']),'LRB',0,'R');
				$this->Cell($w[4],7,number_format($data['sumTotalWeight']),'LRB',0,'R');
				$this->Cell($w[5],7,'Rp. '.number_format($data['sumWorkerCost'],2,',','.'),'LRB',0,'R');
				$this->Cell($w[6],7,'Rp. '.number_format($data['sumPartnerCost'],2,',','.'),'LRB',0,'R');
			}

			$this->Ln(10);
			$this->SetFont('Helvetica','',11);
			
			if($data['typeNum'] == 1){
				$length = $this->GetStringWidth('Terbilang: ' . $data['terbilangAll']);
				$this->Cell($length, 2, 'Terbilang: ' . $data['terbilangAll']);
			}
			else if($data['typeNum'] == 2){
				$length = $this->GetStringWidth('Terbilang: ' . $data['terbilangClient']);
				$this->Cell($length, 2, 'Terbilang: ' . $data['terbilangClient']);
			}
			
			else {
				$length = $this->GetStringWidth('Terbilang: ' . $data['terbilangPartner']);
				$this->Cell($length, 2, 'Terbilang: ' . $data['terbilangPartner']);
			}
			
			$warning = '* Pembayaran via transfer ke rekening atas nama Diamanta Elmira Darda';
			
			$this->Ln(10);
			$this->SetFont('Helvetica','',8);
			$length = $this->GetStringWidth($warning);
			$this->Cell($length, 2, $warning);
			
			$respect = 'Hormat Kami,';
			$company = 'PT LIMAS SENTOSA ANTAR NUSA';
			$director = 'DIAMANTA ELMIRA, SH';
			
			$this->Ln(15);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($respect);
			$this->Cell($length, 2, $respect);
			
			$this->Ln(6);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth($company);
			$this->Cell($length, 2, $company);
			
			$this->Ln(20);
			$this->SetFont('Helvetica','B',11);
			$length = $this->GetStringWidth($director);
			$this->Cell($length, 2, $director);
			
			$this->Ln(6);
			$this->SetFont('Helvetica','',11);
			$length = $this->GetStringWidth('Direktur');
			$this->Cell($length, 2, 'Direktur');
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