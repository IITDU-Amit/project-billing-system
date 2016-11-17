<?php
/**
 * Created by PhpStorm.
 * User: peacefrog
 * Date: 11/17/16
 * Time: 5:45 AM
 */

session_start();
if (isset($_SESSION['email'])) {

} else {
	echo '<script type="text/javascript">location.href = "access_denied.php";</script>';
}

?>

<!DOCTYPE html>
<html>
<head>
	<?php include('include.php');?>
	<title>Create New Bill</title>

</head>
<body>
<div style="width: 1200px; margin: 0 auto;">
	<?php
		include ('header.php');
	?>
	<div class="row" style="height: 550px;">
		<?php include('home_menu.php') ?>
		<div class="col-sm-7" style=" border-left: 1px solid #BBCDBC; border-right: 1px solid #BBCDBC; min-height: 550px;">
			<h2 style="color: #4D574E;">Create New Bill</h2>
			<p id="bill_placement"></p>
		</div>
		<div class="col-sm-3">
			<?php
						include ('show_recent_bills.php');
			?>
		</div>
	</div>
	<?php include ('footer.php');?>
</body>
</html>


<script type="text/javascript">
	/**
	 * Created by peacefrog on 11/17/16.
	 */

	var strJSON =
		'{  "columns" :[' +
		'{ "column":	"No." },'+
		'{ "column":	"Head" },'+
		'{ "column":	"Taka/Credit" },'+
		'{ "column":	"Taka/Student" },'+
		'{ "column":	"No. of credits" },'+
		'{ "column":	"No of students"},'+
		'{ "column": "Contribution(%)" },'+
		'{ "column": "Taka"}],'+
		'"rows" :['+
		'{"row":"CM"},'+
		'{"row":"CE"},'+
		'{"row":"LAE"},'+
		'{"row" : "MQS" },'+
		'{"row" : "FQS" },'+
		'{"row" : "MASC" },'+
		'{"row" : "FASC" },'+
		'{"row" : "GSP" },'+
		'{"row" : "MEQS" },'+
		'{"row" : "MASC" },'+
		'{"row" : "T/S"}]}';

	var calculationDetails = '( 3 * 5 ) * 7;( 4 * 6 ) * 7;( 4 * 6 ) * 7;3 * 7;3 * 7;( 4 * 6 ) * 7;( 4 * 6 ) * 7;( 4 * 6 ) * 7;3 * 7;( 4 * 6 ) * 7;';
	var columnMeasurements = '2,20,7,9,5,7,10,7';

	var obj = JSON.parse(strJSON);
	tableCreate();
	calculate();

	var columnCount;
	var rowCount ;

	function tableCreate() {
		var body = document.getElementById("bill_placement");
		body.setAttribute('class', 'table-responsive');
		var tbl = document.createElement('table');
		tbl.setAttribute('class' , 'table');
		tbl.style.width = '100%';
		tbl.setAttribute('border', '1');
		tbl.setAttribute('id' , 'bill_table');
		columnCount = Object.keys(obj.columns).length;
		rowCount = Object.keys(obj.rows).length;

		var measurements = columnMeasurements.split(',');
		var row = tbl.insertRow(-1);

		for (var i = 0; i < columnCount; i++) {
			var headerCell = document.createElement("TH");
			headerCell.innerHTML = obj.columns[i].column;
			headerCell.setAttribute('width', measurements[i]+'%') ;
			row.appendChild(headerCell);
		}

		//Add the data rows.
		for (var i = 0; i < rowCount; i++) {
			row = tbl.insertRow(-1);
			var cell = row.insertCell(-1);
			cell.innerHTML= (i+1)+'';
			cell = row.insertCell(-1);
			cell.innerHTML = obj.rows[i].row;
			for (var j = 2; j < columnCount; j++) {
				cell = row.insertCell(-1);
				cell.setAttribute('contentEditable' , 'false');

				cell.id='table_cell';
				cell.addEventListener("keydown" , function (event) {
					var key =  event.which;
					key = String.fromCharCode( key );
					var regex = /[0-9\t]|\./;
					if( !regex.test(key) ) {
						event.returnValue = false;
						if(event.preventDefault) event.preventDefault();
					}

					calculateTotalAmount();
				},true);
				cell.addEventListener("keyup" , function (event) {
					calculateTotalAmount();
				}, true)
			}
		}
		addFinalCalculationRows(tbl);
		body.appendChild(tbl)
	}

	function addFinalCalculationRows(tbl) {
		var row = tbl.insertRow(-1);
		var cell = row.insertCell(-1);
		cell.innerHTML= '<strong>Total</strong>';
		cell = row.insertCell(-1);
		cell.setAttribute('colspan' , columnCount-2);
		cell = row.insertCell(-1);
		cell.id = 'total_taka';

		row = tbl.insertRow(-1);
		cell = row.insertCell(-1);
		cell.innerHTML= '<strong>Tax</strong>';
		cell = row.insertCell(-1);
		cell.setAttribute('colspan' , columnCount-2);
		cell.style.textAlign='center';
		cell.innerHTML = '<strong>(10% AIT deduction)</strong>';
		cell = row.insertCell(-1);
		cell.id = 'tax_taka';

		row = tbl.insertRow(-1);
		cell = row.insertCell(-1);
		cell.innerHTML= '<strong>Net</strong>';
		cell = row.insertCell(-1);
		cell.setAttribute('colspan' , columnCount-2);
		cell.style.textAlign='center';
		cell.innerHTML = '<strong>(Taka Only)</strong>';
		cell = row.insertCell(-1);
		cell.id = 'net_taka';
	}

	function calculateTotalAmount() {
		var parsedDetails = calculationDetails.split(';');
		var table = document.getElementById('bill_table');

		for( var i = 1 ; i < rowCount ; i++) {
			var calculationExpression = parsedDetails[(i - 1)].split(' ');
			var rowCells = table.rows.item(i).cells;

			var values = '';

			for(var j = 0 ; j < calculationExpression.length ; j++){

				if(!isNaN(calculationExpression[j])){
					var heading = table.rows.item(0).cells.item(parseInt(calculationExpression[j])-1).innerHTML;

					if(heading.indexOf('%')!= -1){
						values = values.concat(math.chain(rowCells.item(parseInt(calculationExpression[j])-1).innerHTML===''?'0' :rowCells.item(parseInt(calculationExpression[j])-1).innerHTML).divide(100).done());
					}
					else values = values.concat(rowCells.item(parseInt(calculationExpression[j])-1).innerHTML===''?'0' :rowCells.item(parseInt(calculationExpression[j])-1).innerHTML) ;
				}
				else values = values.concat(calculationExpression[j]);
			}

			table.rows.item(i).cells.item(columnCount-1).innerHTML = math.eval(values);

			calculate_total_tax_net(table.rows);
		}

		function calculate_total_tax_net(rows) {
			var subTotal=0;
			var total ;
			var tax ;
			var net ;

			for (var i = 1 ; i < rowCount ; i++){
				if(rows.item(i).cells.item(columnCount-1).innerHTML != '')subTotal = math.chain(subTotal).add(parseFloat(rows.item(i).cells.item(columnCount-1).innerHTML)).done();
			}

			total = subTotal;
			tax = math.chain(total).multiply(.1).done();
			net = total-tax;

			document.getElementById('total_taka').innerHTML= math.format(total,{notation: 'fixed', precision: 2});
			document.getElementById('tax_taka').innerHTML = math.format(tax, {notation: 'fixed', precision: 2});
			document.getElementById('net_taka').innerHTML = math.format(net, {notation: 'fixed', precision: 2});
		}

		function calculate() {
			var parsedDetails = calculationDetails.split(';');

			var table = document.getElementById('bill_table');

			var values = '';
			for( var i = 1 ; i < rowCount ; i++)
			{
				var calculationExpression = parsedDetails[(i-1)].split(' ');
				var rowCells = table.rows.item(i).cells;
				for(var j = 0 ; j < calculationExpression.length ; j++){
					if(!isNaN(calculationExpression[j])){
						values = values.concat(rowCells.item(parseInt(calculationExpression[j])-1).innerHTML.innerHTML+ ' ');
					}
				}
			}
			markActiveButtons();
		}


		function markActiveButtons() {
			var table = document.getElementById('bill_table');
			var parsedDetails = calculationDetails.split(';');

			for( var i = 1 ; i < rowCount ; i++)
			{
				var calculationExpression = parsedDetails[(i-1)].split(' ');

				var rowCells = table.rows.item(i).cells;
				for(var j = 0 ; j < calculationExpression.length ; j++){
					if(!isNaN(calculationExpression[j])){
						var span = rowCells.item(parseInt(calculationExpression[j])-1);
						span.setAttribute('contentEditable' , 'true');
						rowCells.item(parseInt(calculationExpression[j])-1).setAttribute('class' , 'success');
					}
				}
			}

		}
</script>