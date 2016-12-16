<?php
	require_once 'config.php';
	require_once 'utils.php';
	
	class DeliveryList extends FPDF {
		function __construct($orientation='P', $unit='mm', $size='A4'){
			parent::__construct($orientation, $unit, $size);
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
			$columnSizes = array(20, 45, 35, 30, 15, 15, 40, 30, 25, 25);
			
			//Header
			$this->addCell(array_sum($columnSizes), 10, 'PT. LIMAS SENTOSA ANTARNUSA', 'Helvetica', 'B', 12);
			$this->Ln(6);
			
			//Report Title
			$this->addCell(array_sum($columnSizes), 10, 'LAPORAN DAFTAR KIRIM', 'Helvetica', 'U', 14);
			$this->Ln(6);
			
			//Location
			$this->addCell(array_sum($columnSizes), 10, $data['location'], 'Helvetica', 'B', 10);
			$this->Ln(6);
			
			//Report date
			if(isset($data['startDate']) && isset($data['endDate'])){
				$this->Ln(4);
				$dateStr = 'Periode '. Utils::dateIDFormat($data['startDate'], 3).' - '. Utils::dateIDFormat($data['endDate'], 3);
				$this->addCell(array_sum($columnSizes), 10, dateStr, 'Helvetica', 'B', 9);
			}
			
			$this->Ln(10);
			$this->addCells('Helvetica', 'B', 9, $columnSizes, $data['headers']);
			$this->Ln();
			
			$rowNumber = 0;
			
			foreach($data['rows'] as $row){
				$this->Cell($columnSizes[0], 10, $rowNumber++, 0, 0, 'C');
				$this->Cell($columnSizes[1], 10, $row['spbNumber'], 0, 0, 'C');
				$this->Cell($columnSizes[2], 10, $row['sender'], 0, 0, 'C');
				$this->Cell($columnSizes[3], 10, $row['receiver'], 0, 0, 'C');
			}
			
			/*
			// Closing line
			$this->SetFont('Helvetica','B',9);
			$this->Cell($columnSizes[0] + $columnSizes[1] + $columnSizes[2] + $columnSizes[3], 7, 'Total', 'LRB', 0, 'C');
			$this->Cell($columnSizes[4], 7, number_format($data['sum_total_coli']), 'LRB', 0, 'R');
			$this->Cell($columnSizes[5], 7, number_format($data['sum_total_weight']), 'LRB', 0, 'R');
			$this->Cell($columnSizes[6], 7, 'Rp. '.number_format($data['sum_price'], 2, ',' , '.'),'LRB', 0, 'R');	*/
		}
	}
?>