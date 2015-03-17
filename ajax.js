//  http://www.crockford.com/javascript/private.html

//  global, creation defered until use
//  FUTURE: prefix var, allocated during prepocessor?
//  FYI: without var prefix variables are global, allocated when invokation
dungeon_display_file = 'dungeon_ajax.php';
head_display_file =    'head_ajax.php';
nav_display_file =     'nav_ajax.php';

function popUp(URL) {
	day = new Date();
	id = day.getTime();
//	args = 'toolbar = 0, scrollbars = 0, location = 0, statusbar = 0, menubar = 0';
//	args = args + ', menubar = 0, resizable = 0, width = 1024 ,height = 768';
//	eval("page" + id + " = window.open(URL, '" + id + "', args);");
	window.open(URL);
	}

//deprecated?
//function detectKey(event) {
//	if (event.keyCode == 13) { 
//		document['login'].submit();
//		}
//	}

function detectKeyLogin(event) {
	if (event.keyCode == 13) { 
		head_login();
		}
	}

function hideshow0(which) {
	if (!document.getElementById)
		return;
	if (which.style.display == "none")
		which.style.display = "block";
	else if (which.style.display == "block")
		which.style.display = "none";
	}

function showtest(which) {
	if (!document.getElementById)
		return;
	document.getElementById(which).innerHTML =
	  '<center><table style=\"margin: auto;\" id=\"rentab\"><tr><td>[ reset complete  ]</td></tr></table></center>';
	}

function showactive(which) {
	/*  the table with player view is reloaded upon action request,
	    change color of table background to indicate a request is in progress  */
	if (!document.getElementById)
		return;
	document.getElementById(which).style.backgroundColor = 'yellow';
	}

function hideshow(which) {
	if (!document.getElementById)
		return;
	if (document.getElementById(which).style.display == "none")
		document.getElementById(which).style.display = "block";
	else if (document.getElementById(which).style.display == "block")
		document.getElementById(which).style.display = "none";
	}

function head_set(which) {
	//  FUTURE, obsolete function?
	headhq.url = head_display_file+'?ajax=1';
	headhq.div = "head";
	headhq.do_hq();
	}

function head_login() {
	un = document.getElementById("username_dg").value;
	pw = document.getElementById("password").value;
	//alert('username: ' + un + ', ' + pw);
	headhq.url = head_display_file+'?ajax=1&username_dg='+un+'&password='+pw;
	headhq.div = "head";
	headhq.do_now();  //  was .do_hq but, but this works better on slow connections
	//alert('login 2');
 	navhq.do_now();     // refresh navigation controls
 	cal_set('calout');  // refresh dungeon view
	}

function head_logout() {
	//alert('logout');
	headhq.url = head_display_file+'?ajax=0&logout';
	headhq.div = "head";
	headhq.do_now();  //  was .do_hq but, but this works better on slow connections
	//alert('logout 2');
 	navhq.do_now();     // refresh navigation controls
 	cal_set('calout');  // refresh/clear dungeon view
	}

function nav_refresh_alt(which) {
	//navhq.url = nav_display_file+'?ajax=1&cmd=stepforw';
	navhq.url = nav_display_file+'?ajax=0&nav=2';
	navhq.div = "dgnav2";
	navhq.do_now();
	}

function nav_stepback(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=stepback';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_stepleft(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=stepleft';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_steprght(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=steprght';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_newmap(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=newmap';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_dungeon(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=dungeon';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_set(which) {
	//  future: supported GET parameters should be in a config file
	//  future: ghost set/prev buttons until refresh is complete
	calhq.url = dungeon_display_file+'?ajax=1';
	if (y = document.getElementById('year')) {
		if (y.value.length != 0)
			calhq.url = calhq.url + '&year=' + y.value;
		}
	if (m = document.getElementById('month')) {
		if (m.value.length != 0)
			calhq.url = calhq.url + '&month=' + m.value;
		}
	if (d = document.getElementById('day')) {
		if (d.value.length != 0)
			calhq.url = calhq.url + '&day=' + d.value;
		}
	if (w = document.getElementById('weeks')) {
		if ((w.value.length != 0) && (w.value > 0) && (w.value <= 52))
			calhq.url = calhq.url + '&weeks=' + w.value;
		}
	if (b = document.getElementById('bill')) {
		if (b.value.length != 0)
			calhq.url = calhq.url + '&bill=' + b.value;
		}
	if (f = document.getElementById('filter')) {
		if (f.value.length != 0)
			calhq.url = calhq.url + '&filter=' + f.value;
		}
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_doaction(which, a, b, c) {
	var as;
	var ax;
	var ae;                 //  local, preprocessor allocated immediately
	var axc = [];
	var av = 'none';
	var tv = 'none';
	//  FUTURE: convert to post request?
	if (as = document.getElementById('nav2_sel'))
		av = as.value;
	if (ax = document.getElementsByName('target')) {
		for (var i = 0; i < ax.length; i++) {
			if (ax[i].checked) {
				if (ae = document.getElementById('trgt_inp_'+i)) {
					tv = ae.value;
					}
				}
			}
		}
	calhq.url = dungeon_display_file+'?ajax=1&cmd=doaction&act='+av+'&han='+tv;
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_stepforw(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=stepforw';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_stepback(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=stepback';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_stepleft(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=stepleft';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_steprght(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=steprght';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_newmap(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=newmap';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_dungeon(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=dungeon';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_giveup(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=giveup';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_turnrght(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=turnrght';
	calhq.div = "calout";
	calhq.do_hq();
	}

function nav_turnleft(which) {
	calhq.url = dungeon_display_file+'?ajax=1&cmd=turnleft';
	calhq.div = "calout";
	calhq.do_hq();
	}

function cal_prev(which) {
	/*  set previous week, refresh calendar  */
	if (p1 = document.getElementById('pprreevv')) {
		a = p1.innerHTML.split("-");
		if (y = document.getElementById('year')) {
			y.value = a[0];
			}
		if (m = document.getElementById('month')) {
			m.value = a[1];
			}
		if (d = document.getElementById('day')) {
			d.value = a[2];
			}
		cal_set(which);
		}
	}

function to_do_set(which) {
	calhq.url = 'ajax_todo.php';
	calhq.div = "calout";
	calhq.do_hq();
	}

function book_set(which) {
	calhq.url = 'ajax_book.php';
	calhq.div = "calout";
	calhq.do_hq();
	}

function users_set(which) {
	//calhq.url = 'ajax_users.php';
	calhq.url = 'ajax_cvleads.php';
	calhq.div = "calout";
	calhq.do_hq();
	}

function class_hq(param, p2) {
	/*  this is a 'class' to wrap around http_request  */

	/*  consider adding .prototype?
	    http://javascript.about.com/library/bltut35.htm  */

	/*  'constructor' logic goes here  */
	this.div = param;
	this.cb = p2;
	//  delete(this.url);
	//  delete(this.req);
	var this_ref = this;  /*  var here makes this a private property  */

	this.do_hq = function () {
		/*  method - asynchronous content load  */
//		alert('do_hq() ' + this.url);
//		delete this.req;
//		if (typeof(this.req) == 'undefined') {  //  IE7 doesn't support this?
//			alert('req undefined');
			this.req = new XMLHttpRequest();
//			}
		/*  http://www.mikechambers.com/blog/2006/01/31/encapsulating-ajax-xmlhttprequest-calls-within-javascript-classes/  */
		this.req.onreadystatechange = function() { this_ref.do_alert()};
		this.req.open('GET', this.url, true);
		this.req.send(null);
		};

	this.do_alert = function () {
		/*  method - callback for asynchronous content load  */
		//  alert('readyState ' + this.req.readyState);
		if (this.req.readyState == 4) {
			//	alert('do_alert() ' + this.url + ', status' + this.req.status);
			if (this.req.status == 200) {
				document.getElementById(this.div).innerHTML = this.req.responseText;
				//if (this.cb > 0)
				//	alert('ok');
				if (this.cb && typeof(this.cb) === "function")
					this.cb();
				}
			else {
				alert('There was a problem with the request.');
				}
//			delete this.req;
			}
		};

	this.do_now = function () {
//		alert('do_now() ' + this.url);
		/*  method - immediate content load  */
		if (typeof(this.req) == 'undefined') {
//			alert('req undefined');
			this.req = new XMLHttpRequest();
			}
		/*  using false here stalls page load until AJAX call completes  */
		this.req.open('GET', this.url, false);
		this.req.send(null);
		document.getElementById(this.div).innerHTML = this.req.responseText;
//		delete this.req;
		};
	}

function formpop(appt) {
	if (appt == "signup") {
		newWindow = window.open("signup.php",
		  "newWin", "status=yes, width=480, height=240");
		return;
		}
	appt_spl = appt+'';
	if (appt_spl.substring(0, 3) == "new") {
		//  looking for new appt date encoded as: new-2010-11-12
		appt_spl = appt.split("-");
		newWindow = window.open("popup/appt.php?appt=new&year="+appt_spl[1]+"&month="+appt_spl[2]+"&day="+appt_spl[3],
		  "newWin", "status=yes, width=300, height=360");
		}
	else {
		newWindow = window.open("popup/appt.php?appt="+appt,
		  "newWin", "status=yes, width=300, height=360");
		}
	}

function newmap_toggle(which) {
	//  typically this is called for map_bits
	if (!document.getElementById)
		return;
	if (!document.getElementById(which))
		return;
	//  new user, no map, welcome
	if ((mb = document.getElementById(which).value & 15) == 0) {
		newmap.disabled = false;
		document.getElementById('newmap').style.display = '';
		}
	else {
		newmap.disabled = true;
		document.getElementById('newmap').style.display = 'none';
		}
	//  user on home map
	if (mb == 1) {
		dungeon.disabled = false;
		document.getElementById('dungeon').style.display = '';
		}
	else {
		dungeon.disabled = true;
		document.getElementById('dungeon').style.display = 'none';
		}
	//  user on away map
	if (mb & 34) {
		give_up.disabled = false;
		document.getElementById('give_up').style.display = '';
		}
	else {
		give_up.disabled = true;
		document.getElementById('give_up').style.display = 'none';
		}
	//  JJJJ, instead of ajaxing in server side alt nav, look at cal hidden elements, then dynamic javascript to replace/overwrite the nav2 html?
	nav2_div = document.getElementById("dgnav2");  //  FUTURE: what if undefined?
	while (nav2_div.firstChild) {
		//  alert('remove sel');
		nav2_div.removeChild(nav2_div.firstChild);  //  does this handle nested children too?
		}
	if (document.getElementById('nav2_sel')) {
		nav2_sel0 = document.getElementById("nav2_sel");
//		alert('remove sel');
//		while (nav2_sel0.firstChild) {
//			alert('remove sel');
//			nav2_sel0.removeChild(nav2_sel0.firstChild);
//			}
		}
	else {
  	 	nav2_sel0 = document.createElement("select");
		nav2_sel0.setAttribute("id", "nav2_sel");
		}
//	div: nav2_div
//	  select: nav2_sel0
//	    optgroup: nav2_sel_og0
//	      option: nav2_sel_opt
//	nav2_div = document.getElementById("dgnav2");
	nav2_div.appendChild(nav2_sel0);
    	nav2_sel_og0 = document.createElement("optgroup");
	  nav2_sel_og0.setAttribute("label", "Actions");
	  nav2_sel0.appendChild(nav2_sel_og0);
    	nav2_sel_opt = document.createElement("option");
	  nav2_sel_opt.setAttribute("text", "tag");
	  nav2_sel_opt.setAttribute("id", "tag");
	  nav2_sel_opt.text = 'tag';
	  nav2_sel_opt.value = 'tag';
	  nav2_sel_og0.appendChild(nav2_sel_opt);
//	nav2_sel0.appendChild(nav2_sel_opt);
//	delete nav2_sel0;
	if (document.getElementById('trgt_qty')) {
		ajax_test = document.getElementById('trgt_qty').value;
		for (i = 0; i < ajax_test; i++) {
			if (i == 0) {
				if (document.getElementById('nav2_sel')) {
					nav2_sel0 = document.getElementById("nav2_sel");
					}
				}
			if (document.getElementById('trgt_val_'+i)) {
				ajax_test2 = document.getElementById('trgt_val_'+i).value;
    				new_inpt = document.createElement("br");
				  nav2_div.appendChild(new_inpt);
    				new_inpt = document.createElement("input");
				  new_inpt.setAttribute("type", "radio");
				  new_inpt.setAttribute("value", ajax_test2);
				  new_inpt.setAttribute("id", 'trgt_inp_'+i);
				  new_inpt.setAttribute("name", 'target');
				  if (i == 0) new_inpt.setAttribute("checked", "");
				  nav2_div.appendChild(new_inpt);
    				  new_inpt = document.createTextNode(ajax_test2);
				  nav2_div.appendChild(new_inpt);
				}
			}
		}
    	new_inpt = document.createElement("br");
	nav2_div.appendChild(new_inpt);
    	new_inpt = document.createElement("input");
	new_inpt.setAttribute("type", "button");
	new_inpt.setAttribute("value", 'action');
	new_inpt.setAttribute("onclick", "showactive('rentab'); nav_doaction('calout', 'tag', 0, 0);");
	nav2_div.appendChild(new_inpt);
//	http://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit  //  CITATION
	//  document.getElementById('dgnav3').innerHTML = "test";
	if (document.getElementById('log_activity')) {
		ajax_test = document.getElementById('log_activity').innerHTML;
		document.getElementById('dgnav3').innerHTML = ajax_test;
		}
	}

