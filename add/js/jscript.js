$(document).ready(function () 
{
	$('#header').html("<img src='loading1.gif'>");

	$.post("get_date.php", {  }, function (data) {
		$('#header').html(data);
	});
	
	$('#res').html("<img src='loading1.gif'>");

	$.post("get_res.php", {  }, function (data) {
		$('#res').html(data);
	});
		
    $('#GetXml').click(function () {
		$('#get_xml_result').html("<img src='loading1.gif'>");
		$('#header').html("<img src='loading1.gif'>");

        $.post("get_xml.php", {  }, function (data) {
            $('#get_xml_result').text(data);
			
			$.post("get_date.php", {  }, function (data) {
				$('#header').html(data);
			});
        });
    });
	
	$('#CheckData').click(function () {
		$('#check_data_result').html("<img src='loading1.gif'>");
		$('#res').html("<img src='loading1.gif'>");

        $.post("check_data.php", {  }, function (data) {
            $('#check_data_result').text(data);
			
			$.post("get_res.php", {  }, function (data) {
				$('#res').html(data);
			});
        });
    });

    $('#change_prices').on('click', function(e) {
    	e.preventDefault();
    	var $el = $(this).parent();
    	var values = {};
    	$el.find('input:checked').each(function() {
    		var productId = $(this).attr('name');
    		var newPrice = $(this).parents('tr').find('.newprice').text();
    		values[productId] = newPrice;
    	});
    	$.post('changeprices.php?change=yes', 'val='+JSON.stringify(values), function(data) {
    		if(data['error'] != '') {
    			$.each(data, function(i, val) {
    				console.log(val);

    				var $cols = $('#pr'+i).parents('tr').find('td:not(:has(>input))');
    				var $oldPrice = $cols.filter('.oldprice');
    				$oldPrice.text(val['price'] + '/' + $oldPrice.text() );
    				$cols.css({'background-color':'yellow'})
    			});
    		} else {
    			alert(data['error']);
    		}
    	}, 'json');
    	//find('td:not(:has(>input))').css({'background-color':'yellow'})
    });
});

function GetNewProduct()
{
	$('#prod_list').html("<img src='loading1.gif'>");

	$.post("get_new_prod.php", {  }, function (data) {
		$('#prod_list').html(data);
	});
}

function GetChangePrice()
{
	$('#prod_list').html("<img src='loading1.gif'>");

	$.post("get_change_price.php", {  }, function (data) {
		$('#prod_list').html(data);
	});
}

function GetDelProduct()
{
	$('#prod_list').html("<img src='loading1.gif'>");

	$.post("get_del_prod.php", {  }, function (data) {
		$('#prod_list').html(data);
	});
}