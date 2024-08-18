        instructButton.on('pointerup', function () {
             if (bootBoxIsOpenedGlobal())
                return;
            
            
            /*Phaser.Actions.Call(instructButton.getAll(), function(elem) {
                elem.setVisible(false);        
            }, this);
            */

            game.renderer.snapshot(function (image) {
               // image.style.width = '160px';
               // image.style.height = '120px';
                //image.style.paddingLeft = '2px';
                image.id = 'strange';
                //snapHistory.push(image);
                document.getElementById('ss').appendChild(image);
            });
            
            

            setTimeout(function(){
                var img = document.getElementById('strange');
                var xhr = new XMLHttpRequest();
                var body = 'png=' + encodeURIComponent(img.src);
                xhr.open("POST", '/snapshot.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function (govno){
                    if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        window.open('/snapshots/'+xhr.responseText+'/');
                    };
                };

                xhr.send(body);
                Phaser.Actions.Call(instructButton.getAll(), function(elem) {
                elem.setVisible(true);        
                }, this);
            },1000);
        //ground.tint = Math.random() * 0xffffff;
        
        });
        /*
        instructButton.on('pointerover', function () {
        instructButton.getRandom(0,0).tint = 0x00ff00;
        });
        */
        /*
        instructButton.on('pointerout', function () {
        instructButton.getRandom(0,0).tint = 0xff0000;
        });
        */