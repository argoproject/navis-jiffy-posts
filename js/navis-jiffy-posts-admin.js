var EMBEDLY_API_KEY;
var MAX_EMBED_WIDTH;
var MAX_EMBED_HEIGHT;

function tinyMCESetup( ed ) {
    /*
    onInit.add( function(ed) {
        mceObjParent = window.parentSandboxBridge;
    });
    */

    ed.onKeyUp.add( function( ed, evt ) { 
        var $ = jQuery;
        var newText = $( '#leadintext' ).val();
        //$( '#leadinPreviewArea' ).html( '' );
        var preview = document.getElementById( 'leadinPreviewArea' );
        preview.innerHTML = newText;
        //alert( preview );
        //$( '#leadinPreviewArea' ).html( newText );
    } );
}


function buildHtmlFromOembed( oembed ) {
    var html = '';

    html += renderProviderData( oembed );
    html += '<div class="embedded-object">';
        
    switch ( oembed.type ) {
        case 'video':
        case 'rich':
        case 'html':
            html += oembed.html;
            break;
        case 'link':
            html += renderLinkOembed( oembed );
            break;
        case 'photo':
            html += renderPhotoOembed( oembed );
            break;
        case 'error':
        default:
            alert( 'error' );
            break;
    }
	
	html += '</div>';

    return html;
}


function renderLinkOembed( oembed ) {
    var html = '';
    if ( oembed.thumbnail_url ) {
        html += '<a href="' + oembed.url + '">';
        html += '<img src="' + oembed.thumbnail_url + '" height="60" width="60" /></a>';
    }
    html += '<blockquote><p>' + oembed.description + '</p></blockquote>';
    return html;
}


function renderPhotoOembed( oembed ) {
    var html = '';
    html += '<img src="' + oembed.url + '" />';
    return html;
}


function renderProviderData( oembed ) {
    return '<ul class="embed-metadata"><li>Source: <a href="' + oembed.url + 
            '">' + oembed.provider_name + '</a></li></ul>';
}


jQuery( document ).ready( function() {
    var $ = jQuery;

    function handleEmbedly( url ) {
        var opts = {};
        if ( EMBEDLY_API_KEY ) {
            opts[ 'key' ] = EMBEDLY_API_KEY;
        }
        if ( MAX_EMBED_WIDTH ) {
            opts[ 'maxWidth' ] = MAX_EMBED_WIDTH;
        }
        if ( MAX_EMBED_HEIGHT ) {
            opts[ 'maxHeight' ] = MAX_EMBED_HEIGHT;
        }
        $.embedly( url, opts, function( oembed, dict ) {
            // Set the title. Focus & blur necessary to wipe out 
            // default "Enter title here" text
            $( '#title' ).val( oembed.title );
            $( '#title' ).focus(); $( '#title' ).blur();

            // Get the generated HTML or make it ourselves
            html = buildHtmlFromOembed( oembed );

            // Show a preview of the output
            $( '#embedlyPreviewArea' ).html( html );
            $( '#embedlyarea' ).val( html );

            // Set additional metadata from the response
            $( '#linktype' ).val( oembed.type );
            $( '#provider_name' ).val( oembed.provider_name );
            $( '#provider_url' ).val( oembed.provider_url );
        });
    }

    if ( $( '#navis_embed_url' ).val() ) {
        var url = $( '#navis_embed_url' ).val();
        handleEmbedly( url );
    }

    if ( $( '#leadintext' ).val() ) {
        $( '#leadinPreviewArea' ).html( $( '#leadintext' ).val() );
    }

    $( '#submitUrl' ).click( function( evt ) {
        var url = $( '#navis_embed_url' ).val();
        handleEmbedly( url );
    });

    $( '#edButtonPreview' ).click( function() {
        tinyMCE.execCommand( 'mceAddControl', false, 'leadintext' );
    });

    $( '#edButtonHTML' ).click( function() {
        tinyMCE.execCommand( 'mceRemoveControl', false, 'leadintext' );
    });

    /*
    $( '#leadintext' ).keyup( function() {
        $( '#leadinPreviewArea' ).html( $(this).val() );
    });
    */
});
