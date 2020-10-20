/*! jQuery UI - v1.12.1 - 2017-03-31
* http://jqueryui.com
* Copyright jQuery Foundation and other contributors; Licensed  */
!function(a){"function"==typeof define&&define.amd?define(["jquery","./mouse","../version","../widget"],a):a(jQuery)}(function(a){return a.widget("ui.selectable",a.ui.mouse,{version:"1.12.1",options:{appendTo:"body",autoRefresh:!0,distance:0,filter:"*",tolerance:"touch",selected:null,selecting:null,start:null,stop:null,unselected:null,unselecting:null},_create:function(){var b=this;this._addClass("ui-selectable"),this.dragged=!1,this.refresh=function(){b.elementPos=a(b.element[0]).offset(),b.selectees=a(b.options.filter,b.element[0]),b._addClass(b.selectees,"ui-selectee"),b.selectees.each(function(){var c=a(this),d=c.offset(),e={left:d.left-b.elementPos.left,top:d.top-b.elementPos.top};a.data(this,"selectable-item",{element:this,$element:c,left:e.left,top:e.top,right:e.left+c.outerWidth(),bottom:e.top+c.outerHeight(),startselected:!1,selected:c.hasClass("ui-selected"),selecting:c.hasClass("ui-selecting"),unselecting:c.hasClass("ui-unselecting")})})},this.refresh(),this._mouseInit(),this.helper=a("<div>"),this._addClass(this.helper,"ui-selectable-helper")},_destroy:function(){this.selectees.removeData("selectable-item"),this._mouseDestroy()},_mouseStart:function(b){var c=this,d=this.options;this.opos=[b.pageX,b.pageY],this.elementPos=a(this.element[0]).offset(),this.options.disabled||(this.selectees=a(d.filter,this.element[0]),this._trigger("start",b),a(d.appendTo).append(this.helper),this.helper.css({left:b.pageX,top:b.pageY,width:0,height:0}),d.autoRefresh&&this.refresh(),this.selectees.filter(".ui-selected").each(function(){var d=a.data(this,"selectable-item");d.startselected=!0,b.metaKey||b.ctrlKey||(c._removeClass(d.$element,"ui-selected"),d.selected=!1,c._addClass(d.$element,"ui-unselecting"),d.unselecting=!0,c._trigger("unselecting",b,{unselecting:d.element}))}),a(b.target).parents().addBack().each(function(){var d,e=a.data(this,"selectable-item");if(e)return d=!b.metaKey&&!b.ctrlKey||!e.$element.hasClass("ui-selected"),c._removeClass(e.$element,d?"ui-unselecting":"ui-selected")._addClass(e.$element,d?"ui-selecting":"ui-unselecting"),e.unselecting=!d,e.selecting=d,e.selected=d,d?c._trigger("selecting",b,{selecting:e.element}):c._trigger("unselecting",b,{unselecting:e.element}),!1}))},_mouseDrag:function(b){if(this.dragged=!0,!this.options.disabled){var c,d=this,e=this.options,f=this.opos[0],g=this.opos[1],h=b.pageX,i=b.pageY;return f>h&&(c=h,h=f,f=c),g>i&&(c=i,i=g,g=c),this.helper.css({left:f,top:g,width:h-f,height:i-g}),this.selectees.each(function(){var c=a.data(this,"selectable-item"),j=!1,k={};c&&c.element!==d.element[0]&&(k.left=c.left+d.elementPos.left,k.right=c.right+d.elementPos.left,k.top=c.top+d.elementPos.top,k.bottom=c.bottom+d.elementPos.top,"touch"===e.tolerance?j=!(k.left>h||k.right<f||k.top>i||k.bottom<g):"fit"===e.tolerance&&(j=k.left>f&&k.right<h&&k.top>g&&k.bottom<i),j?(c.selected&&(d._removeClass(c.$element,"ui-selected"),c.selected=!1),c.unselecting&&(d._removeClass(c.$element,"ui-unselecting"),c.unselecting=!1),c.selecting||(d._addClass(c.$element,"ui-selecting"),c.selecting=!0,d._trigger("selecting",b,{selecting:c.element}))):(c.selecting&&((b.metaKey||b.ctrlKey)&&c.startselected?(d._removeClass(c.$element,"ui-selecting"),c.selecting=!1,d._addClass(c.$element,"ui-selected"),c.selected=!0):(d._removeClass(c.$element,"ui-selecting"),c.selecting=!1,c.startselected&&(d._addClass(c.$element,"ui-unselecting"),c.unselecting=!0),d._trigger("unselecting",b,{unselecting:c.element}))),c.selected&&(b.metaKey||b.ctrlKey||c.startselected||(d._removeClass(c.$element,"ui-selected"),c.selected=!1,d._addClass(c.$element,"ui-unselecting"),c.unselecting=!0,d._trigger("unselecting",b,{unselecting:c.element})))))}),!1}},_mouseStop:function(b){var c=this;return this.dragged=!1,a(".ui-unselecting",this.element[0]).each(function(){var d=a.data(this,"selectable-item");c._removeClass(d.$element,"ui-unselecting"),d.unselecting=!1,d.startselected=!1,c._trigger("unselected",b,{unselected:d.element})}),a(".ui-selecting",this.element[0]).each(function(){var d=a.data(this,"selectable-item");c._removeClass(d.$element,"ui-selecting")._addClass(d.$element,"ui-selected"),d.selecting=!1,d.selected=!0,d.startselected=!0,c._trigger("selected",b,{selected:d.element})}),this._trigger("stop",b),this.helper.remove(),!1}})});