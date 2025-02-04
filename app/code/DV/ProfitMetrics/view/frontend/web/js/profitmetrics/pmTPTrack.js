document.addEventListener('DOMContentLoaded', function () {
	// config vars:
    if (window._pm_TPTrackEndpoint === undefined) {
        window._pm_TPTrackEndpoint = "/profitmetrics/tracking";
    }

	////

    window._pm_httpPushTPTimer = null;

	let _pm_httpPushTPRetryTimes = 0;

	function _pm_getcookie( cookiename ) {
		cookiename += "=";
		if( document.cookie.indexOf( cookiename ) != -1 ) {
			var idxofSource = document.cookie.indexOf( cookiename ) + cookiename.length;
			var idxofEnd = document.cookie.indexOf( ";", idxofSource );
			var cookval = "";
			if( idxofEnd == -1 ) {
				cookval = document.cookie.substr( idxofSource );
			} else {
				cookval = document.cookie.substr( idxofSource, idxofEnd-idxofSource );
			}
			if( cookval.length != 0 ) {
				return cookval;
			} else {
				return null;
			}
		}
	}

	function _pm_getStoredTPTrack() {
		var ret = _pm_getcookie( "pmTPTrack" );
		if( null != ret && ret.length > 0 ) {
			ret = JSON.parse( decodeURIComponent( ret ) );
		} else {
			ret = { gacid: null, gacid_source: null, fbp: null, fbc: null, timestamp: (((new Date)/1E3|0) - 100) };
		}

		return ret;
	}

	function _pm_httpPushTPTrack() {
		try {
			clearTimeout( _pm_httpPushTPTimer );
		} catch( e ) {
		}

		var _formkey = _pm_getcookie( "form_key" );
		if( null == _formkey ) {
			_pm_httpPushTPTimer = setTimeout( _pm_httpPushTPTrack, 2000 );
		} else {
			var _pm_tpTrackCookVal = _pm_getcookie( "pmTPTrack" );

			var xhttp = new XMLHttpRequest();
			xhttp.timeout = 7500;
			xhttp.onreadystatechange = function() {
				if( this.readyState == 4 && this.status == 200 ) {
					var tsAck = Number(this.responseText);
					var _pm_curPMTPTrack = _pm_getStoredTPTrack();
					if( _pm_curPMTPTrack.timestamp > tsAck ) {
						_pm_httpPushTPRetryTimes++;
						if( _pm_httpPushTPRetryTimes <= 1 ) {
							_pm_httpPushTPTimer = setTimeout( _pm_httpPushTPTrack, 5000 );
						}
					}
				} else if( this.readyState == 4 ) {
					_pm_httpPushTPRetryTimes++;
					if( _pm_httpPushTPRetryTimes <= 1 ) {
						_pm_httpPushTPTimer = setTimeout( _pm_httpPushTPTrack, 5000 );
					}
				}
			};
			xhttp.open( "POST", _pm_TPTrackEndpoint, true );
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send( "tptrack=1&form_key=" + _formkey );
		}
	}

	function _pm_storeTPTrack( tptrack ) {
		var _pm_old_tpTrackCookVal = _pm_getcookie( "pmTPTrack" );
		var _pm_tpTrackCookVal = encodeURIComponent( JSON.stringify( tptrack ) );
		document.cookie = "pmTPTrack=" + _pm_tpTrackCookVal + "; path=/";

		if( _pm_old_tpTrackCookVal != _pm_tpTrackCookVal ) {
			_pm_httpPushTPRetryTimes = 0;
			//_pm_httpPushTPTrack();
		} else {
		}
	}

	var _pm_curPMTPTrack = _pm_getStoredTPTrack();


	var _pm_newFBC = _pm_getcookie( "_fbc" );
	if( null != _pm_newFBC && _pm_curPMTPTrack.fbc != _pm_newFBC ) {
		_pm_curPMTPTrack.fbc = _pm_newFBC;
		_pm_curPMTPTrack.timestamp = (new Date)/1E3|0;
	}

	var _pm_newFBP = _pm_getcookie( "_fbp" );
	if( null != _pm_newFBP && _pm_curPMTPTrack.fbp != _pm_newFBP ) {
		_pm_curPMTPTrack.fbp = _pm_newFBP;
		_pm_curPMTPTrack.timestamp = (new Date)/1E3|0;
	}

	var _pm_newGacid = _pm_getcookie( "_ga" );
	if( null != _pm_newGacid && _pm_curPMTPTrack.gacid_source != "gatracker" && _pm_curPMTPTrack.gacid != _pm_newGacid ) {
		_pm_curPMTPTrack.gacid = _pm_newGacid;
		_pm_curPMTPTrack.gacid_source = "gacookie";
		_pm_curPMTPTrack.timestamp = (new Date)/1E3|0;
	}

	var _curts = (new Date)/1E3|0;
	if( ( _curts - (60*5) ) > _pm_curPMTPTrack.timestamp ) {
		_pm_curPMTPTrack.timestamp;
	}

	_pm_storeTPTrack( _pm_curPMTPTrack );

	function _pm_GetGacidFromTracker() {
		if( typeof ga == 'function' ) {
			try {
				ga(function(tracker) {
					var gacid = tracker.get( 'clientId' );
					if( null != gacid ) {
						var _pm_curPMTPTrack = _pm_getStoredTPTrack();
						if( _pm_curPMTPTrack.gacid != gacid ) {
							_pm_curPMTPTrack.gacid = gacid;
							_pm_curPMTPTrack.gacid_source = "gatracker";
							_pm_curPMTPTrack.timestamp = (new Date)/1E3|0;

							_pm_storeTPTrack( _pm_curPMTPTrack );
						}
					}
				});
			} catch( eee ) {
			}
		} else {
			setTimeout( _pm_GetGacidFromTracker, 100 );
		}
	}
	_pm_GetGacidFromTracker();
});
