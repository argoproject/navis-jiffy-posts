var EMBEDLY_API_KEY;
var MAX_EMBED_WIDTH;
var MAX_EMBED_HEIGHT;

function tinyMCESetup( ed ) {
    /*
    onInit.add( function(ed) {
        mceObjParent = window.parentSandboxBridge;
    });
    */

    /*
    ed.onKeyUp.add( function( ed, evt ) { 
        var $ = jQuery;
        var newText = $( '#leadintext' ).val();
        //$( '#leadinPreviewArea' ).html( '' );
        var preview = document.getElementById( 'leadinPreviewArea' );
        preview.innerHTML = newText;
        //alert( preview );
        //$( '#leadinPreviewArea' ).html( newText );
    } );
    */
}

function renderOembed( oembed ) {
    var $ = jQuery;
    var description = ( $( '#custom_description' ).val() ) ? $( '#custom_description' ).val() : null;

    // Show a preview of the output
    // Get the generated HTML or make it ourselves
    html = buildHtmlFromOembed( oembed, description );
    $( '#embedlyPreviewArea' ).html( html );
    $( '#embedlyarea' ).val( html );

    // Set additional metadata from the response
    $( '#linktype' ).val( oembed.type );
    $( '#provider_name' ).val( oembed.provider_name );
    $( '#provider_url' ).val( oembed.provider_url );

    // Make it possible to remove the image
    if ( $( '#embedlyThumbnail' ) ) {
        $( '#embedlyThumbnail' ).hover( function() {
            $( '#remove-image-icon' ).addClass( 'jiffy-remove' );
            $( '#remove-image-icon' ).click( function( evt ) {
                $( '#embedlyThumbnail' ).detach();
                $( '#remove-image-icon' ).detach();
                $( '#hide_image' ).val( '1' );
                return false;
            });
        },
        function () {
            //$( '#remove-image-icon' ).removeClass( 'jiffy-remove' );
            //$( '#remove-image-icon' ).unbind( 'click', 'removeImage' );
        });
    }
    
    // Make the description editable
    if ( $( '#embedlyDescription' ) ) {
        $( '#embedlyDescription' ).editable( function( value, settings ) {
            return value;
}, {
            type: 'textarea',
            id: 'id',
            name: 'summary',
            tooltip: 'Click to edit description.',
            indicator: 'Updating...',
            style: 'display: inline; width: 80%;',
            rows: 4,
            cols: 40,
            height: 100,
            submit: 'Update',
            callback: function( value, settings ) {
                $( '#custom_description' ).val( value );
            }
        });
    }
}


function buildHtmlFromOembed( oembed, description ) {
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
            html += renderLinkOembed( oembed, description );
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


function renderLinkOembed( oembed, description ) {
    var $ = jQuery;

    var html = '';
    if ( oembed.thumbnail_url && $( '#hide_image' ).val() != 1 ) {
        html += '<a href="#" id="remove-image-icon"></a>';
        html += '<a id="embedlyThumbnailLink" href="' + oembed.url + '">';
        html += '<img id="embedlyThumbnail" src="' + oembed.thumbnail_url + '" height="60" width="60" /></a>';
    }
    html += '<blockquote><p id="embedlyDescription">';
    html += ( description ) ? description : oembed.description;
    html += '</p></blockquote>';
    return html;
}


function renderPhotoOembed( oembed ) {
    var html = '';
    html += '<p><img src="' + oembed.url + '" width="460" /></p>';
    return html;
}


function renderProviderData( oembed ) {
    phtml = '<ul class="embed-metadata">';
    phtml += '<li>Source: <a href="' + oembed.url + '">' + 
            oembed.provider_name + '</a></li>';

    if ( jQuery( '#via_name' ).val() ) { 
        phtml += '<li>Via: <em>';

        if ( jQuery( '#via_url' ).val() )
            phtml += '<a href="' + jQuery( '#via_url' ).val() + '">';

        phtml += jQuery( '#via_name' ).val();

        if ( jQuery( '#via_url' ).val() ) 
            phtml += '</a>';

        phtml += '</li>';
    }
    phtml += '</ul>';
   
    return phtml;
}

function getOembedData() {
    var $ = jQuery; 

    var oembed = {};

    $.each( $( '.embedly_meta' ), function( key, elem ) {
        var name = elem.id.replace( 'embedly_', '' );
        oembed[ name ] = elem.value;
    });

    return oembed;
}

function saveOembedData( oembed ) {
    var $ = jQuery; 
    
    $.each( oembed, function( key, value ) {
        var field_name = 'embedly_' + key;
        $( '#post' ).append( '<input type="hidden" id="' + field_name + 
            '" name="' + field_name + '" value="' + encodeURIComponent( value ) + '" />' );
    });
}

jQuery( document ).ready( function() {
    var $ = jQuery;

    var oembed = getOembedData();
    if ( ! $.isEmptyObject( oembed ) ) {
        renderOembed( oembed );
    }

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
            // Set the title if it's not already set.
            // Focus & blur are necessary to wipe out 
            // default "Enter title here" text
            if ( ! $( '#title' ).val() ) {
                title = '"' + oembed.title + '"';
                $( '#title' ).val( title );
                $( '#title' ).focus(); 
                $( '#title' ).blur();
            }

            renderOembed( oembed );
            saveOembedData( oembed );
        });
    }

    /*
    if ( $( '#navis_embed_url' ).val() ) {
        var url = $( '#navis_embed_url' ).val();
        handleEmbedly( url );
    }
    */

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
