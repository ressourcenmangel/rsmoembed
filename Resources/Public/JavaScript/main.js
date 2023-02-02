var RSM_OEMBED = {
    init: function () {
        RSM_OEMBED.addEvents();
    },
    addEvents: function () {
        document.addEventListener('click', function (click) {
            if (click.target.matches('[data-rsmoembed-uid]')) {
                click.preventDefault();
                //console.log(click.target.dataset.rsmoembedUid);
                var _xhr = new XMLHttpRequest(),
                    _self = click.target,
                    _uid = click.target.dataset.rsmoembedUid,
                    _url = '/?tx_rsmoembed_api[action]=findOne&tx_rsmoembed_api[uid]=' + _uid;

                _xhr.open('GET', _url, true);
                _xhr.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        _self.parentNode.innerHTML = this.responseText;
                    }
                };
                _xhr.send(null);
            }
        }, false);
    }
}
RSM_OEMBED.init();
