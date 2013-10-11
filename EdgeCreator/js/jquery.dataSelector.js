//We use a small helper function that will return true when 'a' is undefined (so we can do if(checkUndefined(data)) return false;
//If we would continue with undefined data we would piss javascript off as we would be getting properties of an
//non-existent object (ie typeof data === 'undefined'; data.fooBar; //throws error
$.expr[':'].data = function (elem, counter, params) {
	var query, operator, key, value, keys, data, i, length, checkAgainst,
		comparators = { // equals, not equals, starts with, ends with, contains
			'=' : function(a,b) { return a === b; },
			'!' : function(a,b) { return a !== b; },
			'^' : function(a,b) { return a.indexOf(b) === 0; },
			'$' : function(a,b) { return a.lastIndexOf(b) === a.length - b.length; },
			'*' : function(a,b) { return a.lastIndexOf(b) !== -1; }
		};

	 //The part in the parenthesis, thus: selector:data( ==>query<== )
	if ( elem === undefined || params === undefined || !( query = params[3]) ) { return false; }

	query = query.split('='); //for dataKey=Value/dataKey.innerDataKey=Value
	key = query[0];
	value = query.length > 1 && query[1] + '' || null;
	
	//We check if the condition was an =, an !=, an $= or an *=
	operator = key.charAt(key.length - 1);
	if( !comparators.hasOwnProperty(operator) ) { return false; }
	else if ( operator !== '=' ) { key = key.substring(0, key.length - 1); }

	//now, drill down through data and make sure key exists
	keys = query[0].split('.');
	data = $(elem).data();
	for(i=0, length = keys.length; i<length; i++) {
		if( data = data[keys[i]] === undefined ) { return false; }
	}
	// if we're still in here, the data key exists
	
	// either return true if no value or the result of the value comparison
	return value === null || comparators[operator](data+'', value);
};