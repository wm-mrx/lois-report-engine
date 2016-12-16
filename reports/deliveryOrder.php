<?php
	require_once 'config.php';
	require_once 'utils.php';
	
	class DeliveryOrder extends FPDF {
		var $userName;
		
		function __construct($orientation='P', $unit='mm', $size='A4'){
			parent::__construct($orientation, $unit, $size);
		}
		
		function setUserName($userName){
			$this->userName = $userName;
		}
		
		function textLine($paragraph, $textWidth, $colWidth){
			$result = array($paragraph);
			$line = floor(($textWidth*strlen($paragraph))/$colWidth);	
			if ($line>0){
				$texts = explode(" ",$paragraph);
				$textWidth = floor($colWidth/ $textWidth);
				$i_data = 0;
				for($x=0;$x<$line+1;$x++) {
					$addText = '';
					$text = '';				
					for($i=$i_data;$i<count($texts);$i++) {					
						$text = $text.$texts[$i];
						$text_len = strlen($text);
						$i_data = $i;
						if ($text_len > $textWidth)
							break;					
						$addText = $text;
						$text = $text.' ';						
					} 
					$result[$x]=$addText;	
				}
			}
			return $result;
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
			$border = 0; // 1 to format setup; 0 running
			$printDate = Utils::dateIDFormat(date("Y-m-d"),1);
			$printTime = date("H:i:s");
			
			/* write pdf */
			foreach($data['rows'] as $report){
				/* print page */
				$this->AddPage();
				
				/*  address line */
				$fontWidth = 3.10;
				$addressLine = $this->textLine($report['receiverAddress'], $fontWidth, 73); //2.145, 51.5
				$sender = $this->textLine($report['sender'].' '.$report['senderContact'], $fontWidth, 45); //2.145, 45
				$receiver = $this->textLine($report['receiver'], $fontWidth, 45); //2.145, 45
				$poLine = $this->textLine($report['poNumber'], $fontWidth, 81.5); //2.145, 81.5
				$marginX=-0; //-4.5
				$marginY=-7; //-9/-22
				$fontBase=2;
						
				$this->Cell(176+$marginY,35+$marginX,'',$border);
				$this->Ln();
				$this->Cell(176+$marginY,4.5,'',$border);
				$this->SetFont('Courier','B',12+$fontBase);
				$this->Cell(40,4.5,$report['spbNumber'],$border);
				$this->Ln();
				$this->Cell(176+$marginY,4.5,'',$border);
				$this->SetFont('Courier','B',9+$fontBase);
				$this->Cell(40,4.5,$printDate,$border);
				$this->Ln();
				$this->Cell(176+$marginY,4.5,'',$border);
				$this->Cell(40,4.5,$printTime,$border);
				$this->Ln();
				$this->Cell(56.7+$marginY,23,'',$border);
				$this->Ln();
				$this->Cell(56.7+$marginY,6.3,'',$border);
				$this->Cell(23.5,6.3,'',$border);
				$this->SetFont('Courier','B',10+$fontBase);
				$this->Cell(45,6.3,isset($sender[0]) ? $sender[0] : ' ',$border,0,'C');
				$this->Cell(44,6.3,'',$border);
				$this->Cell(73,6.3,'',$border);
				$this->Ln();
				$this->Cell(56.7+$marginY,7.3,'',$border);
				$this->Cell(23.5,7.3,'',$border);
				$this->Cell(45,7.3,isset($sender[1]) ? $sender[1] : ' ',$border,0,'C');
				$this->Cell(44,7.3,$report['senderDriver'],$border,0,'C');
				$this->Cell(73,7.3,isset($addressLine[0]) ? $addressLine[0] : ' ',$border,0,'L');
				$this->Ln();
				$this->Cell(56.7+$marginY,5.5,'',$border);
				$this->Cell(23.5,5.5,'',$border);
				$this->Cell(45,5.5,isset($receiver[0]) ? $receiver[0] : ' ',$border,0,'C');
				$this->Cell(44,5.5,'',$border);
				$this->Cell(73,5.5,isset($addressLine[1]) ? $addressLine[1] : ' ',$border,0,'L');
				$this->Ln();
				$this->Cell(56.7+$marginY,8,'',$border);
				$this->Cell(23.5,8,'',$border);
				$this->Cell(45,8,isset($receiver[1]) ? $receiver[1] : ' ',$border,0,'C');
				$this->Cell(44,8,strtoupper($report['destination']),$border,0,'C');
				$this->Cell(73,8,isset($addressLine[2]) ? $addressLine[2] : ' ',$border,0,'L');
				$this->Ln();
				$this->Cell(56.7+$marginY,5.5,'',$border);
				$this->Cell(23.5,5.5,'',$border);
				$this->Cell(45,5.5,$report['receiverPhone'],$border,0,'C');
				$this->Ln();
				$this->Cell(56.7+$marginY,5.7,'',$border);
				$this->Cell(23.5,5.7,'',$border);
				$this->SetFont('Courier','B',12+$fontBase);
				$this->Cell(45,5.7,number_format($report['sumTotalColli']),$border,0,'C');
				$this->SetFont('Courier','B',10+$fontBase);
				/* items 0*/
				if (isset($report['items'][0])){
					$this->Cell(35.5,5.7,strtolower($report['items'][0]['content']),$border,0,'C');
					$this->Cell(20.5,5.7,$report['items'][0]['packing'],$border,0,'C');
					$this->Cell(21,5.7,number_format($report['items'][0]['colli']),$border,0,'C');
					$this->Cell(40,5.7,$report['items'][0]['volume']=='0x0x0'? $report['items'][0]['weight'].'kg':$report['items'][0]['weight'].'kg ('.$report['items'][0]['volume'].')',$border,0,'C');
				}
				$this->Ln();
				$this->Cell(56.7+$marginY,5.7,'',$border);
				$this->Cell(23.5,5.7,'',$border);
				$this->SetFont('Courier','B',12+$fontBase);
				$this->Cell(45,5.7,number_format($report['sumTotalWeight']),$border,0,'C');
				$this->SetFont('Courier','B',10+$fontBase);
				/* items 1*/
				if (isset($report['items'][1])){
					$this->Cell(35.5,5.7,strtolower($report['items'][1]['content']),$border,0,'C');
					$this->Cell(20.5,5.7,$report['items'][1]['packing'],$border,0,'C');
					$this->Cell(21,5.7,number_format($report['items'][1]['colli']),$border,0,'C');
					$this->Cell(40,5.7,$report['items'][1]['volume']=='0x0x0'? $report['items'][1]['weight'].'kg':$report['items'][1]['weight'].'kg ('.$report['items'][1]['volume'].')',$border,0,'C');
				}
				$this->Ln();
				$this->Cell(56.7+$marginY,5.7,'',$border);
				$this->Cell(23.5,5.7,'',$border);
				$this->SetFont('Courier','B',12+$fontBase);
				$this->Cell(45,5.7,$report['additionalCost'] == 0 ? '' : 'Rp. '.number_format($report['additionalCost'],2,',','.'),$border,0,'C');
				$this->SetFont('Courier','B',10+$fontBase);
				/* items 2*/
				if (isset($report['items'][2])){
					$this->Cell(35.5,5.7,strtolower($report['items'][2]['content']),$border,0,'C');
					$this->Cell(20.5,5.7,$report['items'][2]['packing'],$border,0,'C');
					$this->Cell(21,5.7,number_format($report['items'][2]['colli']),$border,0,'C');
					$this->Cell(40,5.7,$report['items'][2]['volume']=='0x0x0'? $report['items'][2]['weight'].'kg':$report['items'][2]['weight'].'kg ('.$report['items'][2]['volume'].')',$border,0,'C');
				}
				$this->Ln();
				$this->Cell(56.7+$marginY,5.7,'',$border);
				$this->Cell(23.5,5.7,'',$border);
				$this->SetFont('Courier','B',12+$fontBase);
				$this->Cell(45,5.7,$report['sumPrice'] ==0 ? '' :'Rp. '.number_format($report['sumPrice'],2,',','.'),$border,0,'C');
				$this->SetFont('Courier','B',10+$fontBase);
				/* items 3*/
				if (isset($report['items'][3])){
					$this->Cell(35.5,5.7,strtolower($report['items'][3]['content']),$border,0,'C');
					$this->Cell(20.5,5.7,$report['items'][3]['packing'],$border,0,'C');
					$this->Cell(21,5.7,number_format($report['items'][3]['colli']),$border,0,'C');
					$this->Cell(40,5.7,$report['items'][3]['volume']=='0x0x0'? $report['items'][3]['weight'].'kg':$report['items'][3]['weight'].'kg ('.$report['items'][3]['volume'].')',$border,0,'C');
				}
				$this->Ln();
				$this->Cell(56.7+$marginY,5.7,'',$border);
				$this->Cell(23.5,5.7,'',$border);
				$this->Cell(45,5.7,$report['paymentMethod']=='CASH ON DELIVERY (COD)'? '(COD)':$report['paymentMethod'] ,$border,0,'C');
				/* items 4*/
				if (isset($report['items'][4])){
					$this->Cell(35.5,5.7,strtolower($report['items'][4]['content']),$border,0,'C');
					$this->Cell(20.5,5.7,$report['items'][4]['packing'],$border,0,'C');
					$this->Cell(21,5.7,number_format($report['items'][4]['colli']),$border,0,'C');
					$this->Cell(40,5.7,$report['items'][4]['volume']=='0x0x0'? $report['items'][4]['weight'].'kg':$report['items'][4]['weight'].'kg ('.$report['items'][4]['volume'].')',$border,0,'C');
				}
				$this->Ln();
				$this->Cell(56.7+$marginY,7,'',$border);
				$this->Cell(23.5,7,'',$border);
				$this->Cell(45,7,$report['pickupDriver'],$border,0,'C');
				$this->Cell(35.5,7,'',$border);
				$this->Cell(81.5,7,$report['note'],$border,0,'C');
				$this->Ln();
				$this->Cell(160.7+$marginY,5.2,'',$border);
				$this->Cell(81.5,5.2,isset($poLine[0]) ? strtolower($poLine[0]) : ' ',$border,0,'C');
				$this->Ln();
				$this->Cell(160.7+$marginY,5.2,'',$border);
				$this->Cell(81.5,5.2,isset($poLine[1]) ? strtolower($poLine[1]) : ' ',$border,0,'C');
				$this->Ln();
				$this->Cell(160.7+$marginY,5.2,'',$border);
				$this->Cell(81.5,5.2,isset($poLine[2]) ? strtolower($poLine[2]) : ' ',$border,0,'C');
				$this->Ln();
				$this->Cell(160.7+$marginY,13,'',$border);
				$this->Cell(30,13,'',$border);
				$this->Ln();
				$this->Cell(160.7+$marginY,5,'',$border);
				$this->Cell(30,5,$data['user'],$border,0,'C');
			}
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