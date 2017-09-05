Effect.BlindRight = function(element) {
  element = $(element);
  var elementDimensions = element.getDimensions();
  return new Effect.Scale(element, 100, Object.extend({
    scaleContent: false,
    scaleY: false,
    scaleFrom: 0,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    restoreAfterFinish: false,
    afterSetup: function(effect) {
      effect.element.makeClipping().setStyle({width: '0px'}).show();
    },
    afterFinishInternal: function(effect) {
      effect.element.undoClipping();
    }
  }, arguments[1] || { }));
};

Effect.BlindLeft = function(element) {
  element = $(element);
  element.makeClipping();
  return new Effect.Scale(element, 0,
    Object.extend({scaleContent: false,
      scaleY: false,
      restoreAfterFinish: true,
      afterFinishInternal: function(effect) {
        effect.element.hide().undoClipping();
      }
    }, arguments[1] || { })
  );
};

Effect.Flip = Class.create();
Object.extend(Object.extend(Effect.Flip.prototype,
  Effect.Base.prototype), {
 
    initialize: function(element, flip_graphic, original_graphic) {
      var options = arguments[3] || {};
 
      this.element = $(element);
      this.flip_graphic = flip_graphic;
      this.original_graphic = original_graphic;
 
      if (this.element.getAttribute('src') == this.flip_graphic) {
        this.flip_graphic = this.original_graphic;
        this.original_graphic = this.element.getAttribute('src');
      }
 
      this.width = options.width || this.element.getWidth();
      this.delta = this.width * 2;
      this.flip = 1;
 
      this.start(options);
    },
 
    update: function(position) {    
 
      var change = this.flip * (0.5 - position) * this.delta;
      var padding = this.width / 2 - change / 2;
 
      this.element.setStyle({
        width: change + 'px',
        padding: '0 0 0 ' + padding + 'px'
      });
 
      if (change < 0)
      {
        this.element.writeAttribute({ src: this.flip_graphic });
        this.flip = -1;
      }
    }
  });