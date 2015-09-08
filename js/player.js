var Player = function () 
{
    function Player(el) 
    {
        this.yt = null;
        this.playing = false;
        this.element = el;
        this.dom = {
            btnPlay:document.getElementById('ytPlay'),
            btnPause:document.getElementById('ytPause'),
            loading:document.getElementById('ytLoading'),
            cntControls: document.getElementsByClassName('ytControls')
        };
        this.init = function()
        {
            var self = this;
            self.onBefore();
            self.yt = new Youtube({
                idYoutube: '',
                quality: 'small',
                idElement: 'ytVideo' //id selector video
            });
            self.yt.ready(function(){
                self.onAfter();
            });
        }
        this.onBefore = function(){
            console.log("inicia");
            this.elements().showLoading();
        }
        this.onAfter = function(){
            console.log("termina");
            this.elements().hideLoading();
            this.elements().showPlay();
            this.bindEvents();
        }
        
    }

    Player.prototype.play = function () {
        this.yt.api.playVideo();
        this.elements().hidePlay();
        this.elements().showPause();
    };
    Player.prototype.pause = function () {
        this.yt.api.pauseVideo();
        this.elements().hidePause();
        this.elements().showPlay();
        
    };
    Player.prototype.test = function () {
        console.log(this.yt.api.getPlaybackQuality());
        console.log(this.yt.api.getVideoBytesTotal());
        console.log(this.yt.api.getVideoUrl());
        console.log(this.yt.api.getVolume());
    };
    Player.prototype.elements = function(){
        var self = this;
        return {
            hideLoading:function(){
                self.dom.loading.style.display = 'none';
            },
            showLoading:function(){
                self.dom.loading.style.display = 'block';
            },
            hidePlay:function(){
                self.dom.btnPlay.style.display = 'none';
            },
            showPlay:function(){
                self.dom.btnPlay.style.display = 'block';
            },
            hidePause:function(){
                self.dom.btnPause.style.display = 'none';
            },
            showPause:function(){
                self.dom.btnPause.style.display = 'block';
            },
            
        }
    }
    Player.prototype.bindEvents = function(){
        console.log(this.play);
        var self = this;
        this.dom.btnPlay.addEventListener('click', function(){
            self.play();
        });
        this.dom.btnPause.addEventListener('click', function(){
            self.pause();
        });
    }
    return Player;
}();