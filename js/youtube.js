var Youtube = function () 
{
    function Youtube(obj) 
    {
        this.api = {};
        this.opt = obj;
        this.mytimer = null;
        this.currentTime = 0;
        this.tmp = {};
    }
    Youtube.prototype.loadApi = function (cb) 
    {
        var tag;
        tag = document.createElement('script');
        tag.src = '//www.youtube.com/iframe_api';
        tag.onload = function () {
            return new window.YT.ready(function () {
                cb(window.YT);
            });
        };
        document.head.appendChild(tag);
    };
    Youtube.prototype.onDuration = function(cb){
        this.tmp.duration = cb;
    }
    Youtube.prototype.ready = function (cb) {
        var self = this;
        this.loadApi(function (yt) 
        {
            self.api = new yt.Player(self.opt.idElement, {
                width: '100',
                height: '100',
                videoId: self.opt.idYoutube,
                playerVars: {
                    controls: 0,
                    showinfo: 0,
                    modestbranding: 1,
                    wmode: 'transparent'
                },
                events: {
                    'onReady': function (e) {
                        e.target.setPlaybackQuality(self.opt.quality);
                        cb(e)
                    },
                    'onStateChange': function (e) {

                        switch (e.data) 
                        {
                            case yt.PlayerState.BUFFERING:
                                e.target.setPlaybackQuality(self.opt.quality);
                                break;
                        
                            case yt.PlayerState.PLAYING:
     
                                self.mytimer = setInterval(function() 
                                {
                                    self.tmp.duration(e.target.getCurrentTime(), e.target.getDuration());
                                }, 1000);
                                
                                break;
                            default:
                                clearInterval(self.mytimer);
                        }
                        
                    }
                }
            });
        });
    };
    return Youtube;
}();



if(typeof duracionJpmaster == 'undefined') duracionJpmaster = {};
duracionJpmaster.timeFormat = {
    showHour: false,
    showMin: true,
    showSec: true,
    padHour: false,
    padMin: true,
    padSec: true,
    sepHour: ":",
    sepMin: ":",
    sepSec: ""
};
duracionJpmaster.convertTime = function(s) {
    var myTime = new Date(s * 1000);
    var hour = myTime.getUTCHours();
    var min = myTime.getUTCMinutes();
    var sec = myTime.getUTCSeconds();
    var strHour = (duracionJpmaster.timeFormat.padHour && hour < 10) ? "0" + hour : hour;
    var strMin = (duracionJpmaster.timeFormat.padMin && min < 10) ? "0" + min : min;
    var strSec = (duracionJpmaster.timeFormat.padSec && sec < 10) ? "0" + sec : sec;
    return ((duracionJpmaster.timeFormat.showHour) ? strHour + duracionJpmaster.timeFormat.sepHour : "") + ((duracionJpmaster.timeFormat.showMin) ? strMin + duracionJpmaster.timeFormat.sepMin : "") + ((duracionJpmaster.timeFormat.showSec) ? strSec + duracionJpmaster.timeFormat.sepSec : "");
};


