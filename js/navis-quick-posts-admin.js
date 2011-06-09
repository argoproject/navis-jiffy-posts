var EMBEDLY_API_KEY;

function buildHtmlFromOembed( oembed ) {
    var html = '';
    
    switch ( oembed.type ) {
        case 'video':
        case 'rich':
        case 'html':
            html = oembed.html;
            break;
        case 'link':
            html = renderLinkOembed( oembed );
            break;
        case 'photo':
            html = renderPhotoOembed( oembed );
            break;
        case 'error':
        default:
            alert( 'error' );
            break;
    }

    return html;
}


function renderLinkOembed( oembed ) {
    var html = '';
    if ( oembed.thumbnail_url ) {
        html += '<div class="module image left" width="' + 
            oembed.width + '">';
        html += '<img src="' + oembed.thumbnail_url + '" height="' +
            oembed.height + '" width="' + oembed.width + '" ' +
            'class="alignleft" />';
        html += '</div>';
    }
    html += '<p><blockquote>' + oembed.description + '</blockquote></p>';
    return html;
}


function renderPhotoOembed( oembed ) {
    var html = '';
    html += '<img src="' + oembed.url + '" />';
    return html;
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

            // Add the embed to the TinyMCE visual editor
            //window.send_to_editor( html );

            // Also, show a preview of the output
            $( '#embedlyPreviewArea' ).html( html );
            $( '#embedlyarea' ).val( html );
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

    $( '#leadintext' ).keyup( function() {
        $( '#leadinPreviewArea' ).html( $(this).val() );
    });
});
