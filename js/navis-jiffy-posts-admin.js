var EMBEDLY_API_KEY;
var MAX_EMBED_WIDTH;
var MAX_EMBED_HEIGHT;
var oembed; // gross

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
            rows: 6,
            cols: 30,
            //height: 100,
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

    $( '.jiffypost' ).addClass( 'jiffy-link' );

    var html = '';
    if ( oembed.thumbnail_url && $( '#hide_image' ).val() != 1 ) {
        html += '<a href="#" id="remove-image-icon"></a>';
        html += '<a id="embedlyThumbnailLink" href="' + oembed.url + '">';
        html += '<img id="embedlyThumbnail" src="' + oembed.thumbnail_url + '" height="60" width="60" /></a>';
    }
    html += '<blockquote><p id="embedlyDescription">';
    html += ( description ) ? description : oembed.description;
    html += '</p></blockquote>';

    var domain = urlToDomain( oembed.url );

    if ( domain ) {
        html += '<p class="jiffy-sourceurl">&#8212; <a href="' + oembed.url + '">' + domain + '</a></p>';
    }

    return html;
}


function renderPhotoOembed( oembed ) {
    var $ = jQuery;

    var html = '';
    linkUrl = $( '#navis_embed_url' ).val();
    html += '<p><a href="' + linkUrl + '">';
    html += '<img src="' + oembed.url + '" width="460" /></a></p>';
    return html;
}


function urlToDomain( url ) {
    // XXX: needs hardening
    var parts = url.split( '/' );
    return parts[ 2 ];
}


function renderProviderData( oembed ) {
    var $ = jQuery;
    var domain = urlToDomain( oembed.url );

    phtml = '<ul class="embed-metadata">';
    phtml += '<li class="jiffy-icon">';
    phtml += '<img src="http://s2.googleusercontent.com/s2/favicons?domain=' + 
        domain + '" alt="jiffy-icon" width="16" height="16" /></li>';
    phtml += '<li>Source: <a href="' + oembed.url + '">' + 
            oembed.provider_name + '</a></li>';

    if ( $( '#via_name' ).val() ) { 
        phtml += '<li>Via: <em>';

        if ( $( '#via_url' ).val() )
            phtml += '<a href="' + $( '#via_url' ).val() + '">';

        phtml += $( '#via_name' ).val();

        if ( $( '#via_url' ).val() ) 
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

    oembed = getOembedData();
    if ( ! $.isEmptyObject( oembed ) ) {
        renderOembed( oembed );
    }

    function handleEmbedly( url ) {
        var opts = {};
        if ( EMBEDLY_API_KEY ) {
            opts[ 'key' ] = EMBEDLY_API_KEY;
        }
        opts[ 'maxWidth' ]  = 460;
        opts[ 'maxHeight' ] = 640;

        /*
         * XXX: hardcoding embed widths for now
        if ( MAX_EMBED_WIDTH ) {
            opts[ 'maxWidth' ] = MAX_EMBED_WIDTH;
        }
        if ( MAX_EMBED_HEIGHT ) {
            opts[ 'maxHeight' ] = MAX_EMBED_HEIGHT;
        }
        */
        $.embedly( url, opts, function( oembedResponse, dict ) {
            // Set the title if it's not already set.
            // Focus & blur are necessary to wipe out 
            // default "Enter title here" text
            oembed = oembedResponse; // gross
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

    if ( $( '#leadintext' ).val() ) {
        $( '#leadinPreviewArea' ).html( $( '#leadintext' ).val() );
    }
    /*
    $( '#leadintext' ).keyup( function() {
        $( '#leadinPreviewArea' ).html( $(this).val() );
    });
    */

    $( '#submitUrl' ).click( function( evt ) {
        var url = $( '#navis_embed_url' ).val();
        handleEmbedly( url );
        return false; // to prevent the form from trying to submit
    });

    $( '#edButtonPreview' ).click( function() {
        tinyMCE.execCommand( 'mceAddControl', false, 'leadintext' );
    });

    $( '#edButtonHTML' ).click( function() {
        tinyMCE.execCommand( 'mceRemoveControl', false, 'leadintext' );
    });

    $( '#post' ).submit( function( evt ) {
        $( '#embedlyarea' ).val( $( '#embedlyPreviewArea' ).html() );
    });
    
    $( '#via_name' ).blur( function( evt ) {
        renderOembed( oembed );
    });
    $( '#via_url' ).blur( function( evt ) {
        renderOembed( oembed );
    });
});
