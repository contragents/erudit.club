function getShareButton(X, Y, _this) {
			var podel=[15,14,4,5,11,8,18,28,17,31];
			var P = [];
			
			fishka = _this.add.image(0, 0, 'fishka_empty','fishka_empty_small');
			fishka.tint = Math.random() * 0xffffff;
			fishka.displayWidth = 260;
			fishka.displayHeight = 32;
			const correction=1.2;
			var frames = atlasTexture.getFrameNames();
			for (var i = 0; i < podel.length; i++) {
				P[i] = _this.add.image(0, 0, 'megaset', frames[podel[i]]);
				P[i].displayHeight = fishka.displayHeight/correction;
				P[i].scaleX = P[i].scaleY;
				P[i].setOrigin(.5, .5);
				P[i].x=fishka.displayWidth-330+i*20-P[i].displayWidth;
				P[i].y=fishka.displayHeight-5-P[i].displayHeight;
				if (podel[i] == 31) P[i].y += 2;//буква я както смещена
			}
			var container = _this.add.container(X, Y, [fishka, P[0], P[1], P[2], P[3], P[4], P[5], P[6], P[7], P[8], P[9]]);
			container.setSize(fishka.displayWidth,fishka.displayHeight);
			container.setInteractive();
			return container;
		}