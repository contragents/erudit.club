
function getButton(X, Y, width, text, _this) {
			var buttonText=text.split('');
			var P = [];
			
			fishka = _this.add.image(0, 0, 'fishka_empty','fishka_hor_center');
			fishka.tint = 0x00ff00;//Math.random() * 0xffffff;
			fishka.displayWidth = width;
			fishka.displayHeight = 32*2;
			const correction = 1.2/1.4;
			const letterStep = 20*2;
			const baseCenter = fishka.displayWidth - 30*2*1.3;
			var frames = atlasTexture.getFrameNames();
			var elements = [fishka];
			for (var i = 0; i < buttonText.length; i++) {
				P[i] = _this.add.image(0, 0, 'megaset', frames[rusLetters.get(buttonText[i])]);
				P[i].displayHeight = fishka.displayHeight/correction;
				P[i].scaleX = P[i].scaleY;
				P[i].setOrigin(.5, .5);
				P[i].x=fishka.displayWidth-baseCenter-buttonText.length*letterStep/2+i*letterStep-P[i].displayWidth;
				P[i].y=fishka.displayHeight-5*2-P[i].displayHeight/1.4;
				if (rusLetters.get(buttonText[i]) == 31) P[i].y += 2*2.1;//буква я както смещена
				elements[i+1]=P[i];
			}
			var container = _this.add.container(X, Y, elements);//[fishka, P[0], P[1], P[2], P[3], P[4], P[5], P[6], P[7], P[8], P[9]]);
			container.setSize(fishka.displayWidth,fishka.displayHeight);
			container.setInteractive();
			return container;
		}