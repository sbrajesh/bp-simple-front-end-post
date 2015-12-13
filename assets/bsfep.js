jQuery( document ).ready( function () {
	
	if ( typeof wp == 'undefined' || typeof wp.media == 'undefined' || typeof wp.media.model == 'undefined' || typeof wp.media.view == 'undefined' ) {
		return ;
	}
	
	var post_id = jQuery( '#post_ID').val();
	var nonce = jQuery( "#_bsfep_media_uploader_nonce").val();
	wp.media.model.settings.post.id = wp.media.view.settings.post.id= post_id;
	wp.media.model.settings.post.nonce = wp.media.view.settings.post.nonce= nonce;
		//= wp.media.view.settings.post;
});

/**
 * Compat for using Featured Post thumbnail
 * A copy from post.js
 * @param {type} nonce
 * @returns {undefined}
 */
WPRemoveThumbnail = function(nonce){
	jQuery.post(ajaxurl, {
		action: 'set-post-thumbnail', post_id: jQuery( '#post_ID' ).val(), thumbnail_id: -1, _ajax_nonce: nonce, cookie: encodeURIComponent( document.cookie )
	}, function(str){
		if ( str == '0' ) {
			alert( 'There was a problem. Please try again' );
		} else {
			WPSetThumbnailHTML(str);
		}
	}
	);
};

WPSetThumbnailHTML = function(html){
	jQuery('.inside', '#postimagediv').html(html);
};
