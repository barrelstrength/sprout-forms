var element = $('#tab-entries').jScrollPane({
	showArrows: true,
	hideFocus: true
});

var api = element.data('jsp');

function getContentHeight() {
	var viewHeight = $( window ).height();
	var viewHeight = (viewHeight/10)*6.5;
	
	if (viewHeight > 500) {
	
		$("#tab-entries").css({
			"height": viewHeight
		});
		var posX = api.getContentPositionX();
		
		setTimeout(function(){api.scrollToX(0, 0)}, 100);
		setTimeout(function(){api.reinitialise()}, 400);
		setTimeout(function(){renderEntriesHeadings()}, 400);
	} else {
		$("#tab-entries").css({
			"height": "500px"
		});
	}
}

function checkSelectedTab() {

	// Avoid future issues with pane padding changing by making content negative margin dynamic
	var panePaddingLeft = $('.pane').css('padding-left');
	var panePaddingRight = $('.pane').css('padding-right');
	var panePaddingBottom = $('.pane').css('padding-bottom');

	if ($("#tab-formEntries").hasClass("sel")) {
	
		$('.bsd-branding').addClass('hidden');
		
		$('#content').css({
			'margin-left' : '-' + panePaddingLeft,
			'margin-right' : '-' + panePaddingRight,
			'margin-bottom' : '-' + panePaddingBottom,
			'overflow' : 'hidden'
		});
		$('.content').addClass('entries');
		renderEntriesHeadings();
		renderLeftBorder();
	} else {
		$('.bsd-branding').removeClass('hidden');
		$('.content').removeClass('entries');
		$('#content').css({
			'margin' : '0 0 0 0'
		});
	}
}

function renderEntriesHeadings() {
	var dateHeight = $("th.date").innerHeight();
	$('#tab-entries').bind({
		'jsp-scroll-x': function(event, scrollPositionX, isAtLeft, isAtRight) {
		
			$(".left-border").css({
				"left" : scrollPositionX
			});
			
			$(".date").css({
				"left" : scrollPositionX + 3
			});
			
			
		},
		
		'jsp-scroll-y': function(event, scrollPositionY, isAtTop, isAtBottom) {
			$("th div").css({
				"top" : scrollPositionY
			});
		}
		
	});
	
	
	
	$("th > div").each(function() {
		if ($(this).parent("th").hasClass("padding")) {
			
			var divWidth = ($('.date').innerWidth()) + ($('.left-border').innerWidth());
		} else {
			var divWidth = $(this).parent("th").outerWidth();
			//alert(divWidth);
		}
	
		
		var divHeight = $(this).parent("th").innerHeight();
		
		$(this).css({
			"width" : divWidth - 10,
			"height": divHeight
		});
	});	
	
	$("th.date").css({
		"height" : dateHeight
	});	
}

function checkDeletePosition() {
	setTimeout(function(){
		if (api.getIsScrollableH() == true) {
			$('#tab-entries').bind({
				'jsp-scroll-x': function(event, scrollPositionX, isAtLeft, isAtRight) {
			
		
					$("table#entries td.delete").css({
						"right" : (-scrollPositionX)+16
					});
					
				}
			});
		}
		
		if (api.getIsScrollableH() == false) {
			$("table#entries td.delete").css({
				"right" : '0'
			});
		}
	}, 500);
}

function renderLeftBorder() {
	
	$(".left-border").each(function() {
		var parentHeight = $(this).parent('tr').outerHeight();
		
		$(this).children('div').css('height', parentHeight);
		
	});
}


$(document).ready(function() {
	getContentHeight();
	checkSelectedTab();
	$('.tabs a').click(function(event) {
		checkSelectedTab();
	});
});

$(window).load(function() {
	checkDeletePosition();
});

$( window ).resize(function() {
	getContentHeight();
	
	checkDeletePosition();
	
});