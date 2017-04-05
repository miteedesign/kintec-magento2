var config = {

	map: {
		'*': {
			'alothemes': 'Magiccart_Alothemes/js/alothemes',
		},
	},

	paths: {
		'magiccart/easing'		: 'Magiccart_Alothemes/js/plugins/jquery.easing.min',
		'magiccart/ddslick'		: 'Magiccart_Alothemes/js/plugins/jquery.ddslick',
		'magiccart/fancybox'		: 'Magiccart_Alothemes/js/plugins/jquery.fancybox.pack',
		'magiccart/socialstream'	: 'Magiccart_Alothemes/js/plugins/jquery.socialstream',
		'magiccart/slick'			: 'Magiccart_Alothemes/js/plugins/slick.min',
		'magiccart/zoom'			: 'Magiccart_Alothemes/js/plugins/jquery.zoom.min',
		// 'alothemes'		: 'Magiccart_Alothemes/js/alothemes',
	},

	shim: {
		'magiccart/easing': {
			deps: ['jquery']
		},
		'magiccart/ddslick': {
			deps: ['jquery']
		},
		'magiccart/fancybox': {
			deps: ['jquery']
		},
		'magiccart/socialstream': {
			deps: ['jquery']
		},
		'magiccart/slick': {
			deps: ['jquery']
		},
        'alothemes': {
            deps: ['jquery', 'magiccart/easing', 'magiccart/fancybox', 'magiccart/ddslick', 'magiccart/slick']
        },

	}

};
