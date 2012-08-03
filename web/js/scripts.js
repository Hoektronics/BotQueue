

    	$(function(){

			$('.slider')._TMS({

				prevBu:false,

				nextBu:false,

				playBu:'.play',

				duration:1000,

				easing:'easeOutQuad',

				preset:'slideFromtop',

				pagination:true,

				pagNums:false,

				slideshow:3000,

				numStatus:false,

				banners:'fade',// fromLeft, fromRight, fromTop, fromBottom

				waitBannerAnimation:false,

				progressBar:false

			});

			$(".social a").easyTooltip();

		})

		function goToByScroll(id){

     		$('html,body').animate({scrollTop: $("#"+id).offset().top},'slow');

		}

