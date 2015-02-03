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

	$('#select_all').on('click', function(e) {
		var chkdVal = $(e.target).prop('checked');
		$('tr.elem.visible .checker').prop('checked', chkdVal);
	});

	$('#search>input').on('keypress', function(e) {
		var keycode = (e.keyCode ? e.keyCode : e.which);

		if(keycode == '13'){
			var sText = $(this).val();

			if(sText != '') {
				$('tr.elem').addClass('hide').removeClass('visible');
				$('input:checked').prop('checked', false);
				searchText($(this).val());
			} else {
				$('tr.elem').removeClass('hide').addClass('visible');
			}
		}
		
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
    		if(typeof data['error'] == 'undefined') {
    			$.each(data, function(i, val) {

    				var $cols = $('#pr'+i).prop('checked',false).parents('tr').find('td:not(:has(>input))');
    				var $oldPrice = $cols.filter('.oldprice');
    				$oldPrice.text(val['price'] + '/' + $oldPrice.text() );
    				$cols.css({'background-color':'yellow'})
    			});
    		} else {
    			alert(data['error']);
    		}
    	}, 'json');
    });

    $('#to_first, #go_away').on('click', function(e) {
    	e.preventDefault();
    	var actId = $(this).attr('id');
    	var $el = $(this).parent();
    	var values = [];
    	$el.find('input:checked').each(function() {
    		var productId = $(this).attr('name');
    		values.push(productId);
    	});

    	$.post('changeprices.php?move=' + actId, 'val='+JSON.stringify(values), function(data) {
    		if(data === null) {
    			alert('Пустой ответ сервера');
    			return false;
    		}

    		if(typeof data['error'] == 'undefined') {

    			if(data['action'] == 'highlight') {
    				$.each(data['elements'], function(i, val) {

    					var $cols = $('#pr'+val).prop('checked',false).parents('tr').find('td');

    					$cols.css({'background-color':'yellow'});    				
    				});
    			} else if(data['action'] == 'remove') {

    				$.each(data['elements'], function(i, val) {
    					$('#pr'+val).parents('tr').remove();		
    				});
    			}    			


    		} else {
    			alert(data['error']);
    		}
    	}, 'json');
    });
});

function searchText(text) {
	var res = {};
	var sLen = text.length - 1;
	var elems = [];

	if(text.indexOf('/') == 0 && text.lastIndexOf('/') == sLen) {
		text = new RegExp(text.substring(1, text.length - 1));
		elems = matchReg(text);
	} else {
		elems = hasText(text);
	}

	jQuery.each(elems, function(i, val) {
			$('#pr'+val).parents('tr').removeClass('hide').addClass('visible');
	});
	
}

function matchReg(regText) {
	var matchText = [];

	for( var i in colTexts) {
		if(regText.test(colTexts[i])) {
			matchText.push(i);
		}
	}

	return matchText;
}

function hasText(text) {
	var matchText = [];

	for( var i in colTexts) {
		if(colTexts[i].indexOf(text) != -1) {
			matchText.push(i);			
		}
	}
	return matchText;
}

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