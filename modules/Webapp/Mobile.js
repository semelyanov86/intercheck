if(typeof($) == 'undefined') {
	$ = function(id) {
		var node = document.getElementById(id);
		if(typeof(node) == 'undefined') node = false;
		return node;
	}
	$fnT = function(id1, id2) {
		var node1 = $(id1);
		var node2 = $(id2);
		if(node1) node1.style.display = 'none';
		if(node2) node2.style.display = 'block';
	}
	$fnFocus = function(id) {
		try {
			var node = $(id);
			node.focus();
		} catch(error) {
		}
	}
	
	$fnAddClass = function(node, toadd) {
		var classValue = node.className;
		var regex = new RegExp(toadd, "g");
		if(classValue.match(regex) == null) {
			classValue += " " + toadd;
			node.className = classValue;
		}
	}
	$fnRemoveClass = function(node, toremove) {
		var classValue = node.className;
		var regex = new RegExp(toremove, "g");
		classValue = classValue.replace(regex, '');
		node.className = classValue;
	}
	
	$fnCheckboxOn = function(idprefix) {
		//$fnT((idprefix+'_on'), (idprefix+'_off'));
		
		var nodeon = $(idprefix+'_on');
		var nodeoff = $(idprefix+'_off');
		if(nodeon) $fnAddClass(nodeon.parentNode, 'hide');
		if(nodeoff) $fnRemoveClass(nodeoff.parentNode, 'hide');
	}
	$fnCheckboxOff = function(idprefix) {
		//$fnT((idprefix+'_off'), (idprefix+'_on'));
		
		var nodeon = $(idprefix+'_on');
		var nodeoff = $(idprefix+'_off');
		if(nodeon) $fnRemoveClass(nodeon.parentNode, 'hide');
		if(nodeoff) $fnAddClass(nodeoff.parentNode, 'hide');
	}
}
