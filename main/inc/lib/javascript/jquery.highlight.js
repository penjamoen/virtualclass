/*

highlight v3

Highlights arbitrary terms.

<http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>

MIT license.

Johann Burkard
<http://johannburkard.de>
<mailto:jb@eaio.com>

*/

jQuery.fn.highlight = function(pat,real_code) {
	
 function removeAccents(text) {
	
	 text = text.replace(new RegExp("[àáâãäå]", 'g'),"a");
	 text = text.replace(new RegExp("ç", 'g'),"c");
	 text = text.replace(new RegExp("[èéêë]", 'g'),"e");
	 text = text.replace(new RegExp("[ìíîï]", 'g'),"i");
	 text = text.replace(new RegExp("ñ", 'g'),"n");                            
	 text = text.replace(new RegExp("[òóôõö]", 'g'),"o");
	 text = text.replace(new RegExp("[ùúûü]", 'g'),"u");
	 text = text.replace(new RegExp("[ýÿ]", 'g'),"y");
	 return text;
	 
 }
	
 function innerHighlight(node, pat) {
	 
  //remove accents from 
	 
  var skip = 0;
  if (node.nodeType == 3) {
	  
   //remove accents from text to test
   var textToTest = node.data.toLowerCase();
   textToTest = removeAccents(textToTest);
   textToTest = textToTest.toUpperCase();
   
   //remove accents from pattern
   pat = pat.toLowerCase();
   pat = removeAccents(pat);
   pat = pat.toUpperCase();
   
   pos = textToTest.indexOf(pat);
   
   if (pos >= 0) {
    var spannode = document.createElement('a');
    spannode.className = 'glossary-ajax';
    spannode.style.color = '#084B8A';
    spannode.style.fontWeight='100';
	spannode.style.textDecoration = 'none';
    spannode.name = 'link'+real_code;
    spannode.href = '#';

    var real_word = textToTest;
    var middlebit = node.splitText(pos);
    var real_word_data = new Array();
    var my_condition = new RegExp('('+ pat +')\\b([\\s|\\W]|\\D)*');
    cant = 0;
    if($.browser.msie ) {
        cant = 1;
    } else {
        cant = 2;
    }
    real_word_data = real_word.toUpperCase().split(my_condition);
    if (real_word_data.length >= cant) {
	    var endbit = middlebit.splitText(pat.length);
	    var middleclone = middlebit.cloneNode(true);
	    spannode.appendChild(middleclone);
	    middlebit.parentNode.replaceChild(spannode, middlebit);
	    skip = 1;
    } else {
    	return true;
    }

   }
  } else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
   for (var i = 0; i <node.childNodes.length ; ++i) {
    i += innerHighlight(node.childNodes[i], pat);
   }
  }
  return skip;
 }
 return this.each(function() {
  innerHighlight(this, pat.toUpperCase());
 });
};

jQuery.fn.removeHighlight = function() {
 function newNormalize(node) {
    for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
        var child = children[i];
        if (child.nodeType == 1) {
            newNormalize(child);
            continue;
        }
        if (child.nodeType != 3) { continue; }
        var next = child.nextSibling;
        if (next == null || next.nodeType != 3) { continue; }
        var combined_text = child.nodeValue + next.nodeValue;
        new_node = node.ownerDocument.createTextNode(combined_text);
        node.insertBefore(new_node, child);
        node.removeChild(child);
        node.removeChild(next);
        i--;
        nodeCount--;
    }
 }

 return this.find("a.highlight").each(function() {
    var thisParent = this.parentNode;
    thisParent.replaceChild(this.firstChild, this);
    newNormalize(thisParent);
 }).end();
};