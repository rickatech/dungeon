//  http://www.crockford.com/javascript/private.html

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

function detectKey(event) {
	if (event.keyCode == 13) { 
		document['login'].submit();
		}
	}

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
	  '<center><table style=\"margin: auto;\"><tr><td>[ reset complete  ]</td></tr></table></center>';
	}

function showactive(which) {
	if (!document.getElementById)
		return;
	document.getElementById(which).style.backgroundColor = 'blue';
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
	headhq.url = head_display_file+'?ajax=1';
	headhq.div = "head";
	headhq.do_hq();
	}

function head_login() {
	un = document.getElementById("username").value;
	pw = document.getElementById("password").value;
	//alert('username: ' + un + ', ' + pw);
	headhq.url = head_display_file+'?ajax=1&username='+un+'&password='+pw;
	headhq.div = "head";
	headhq.do_hq();
	//alert('login 2');
 	navhq.do_now();     // refresh navigation controls
 	cal_set('calout');  // refresh dungeon view
	}

function head_logout() {
	//alert('logout');
	headhq.url = head_display_file+'?ajax=0&logout';
	headhq.div = "head";
	headhq.do_hq();
	//alert('logout 2');
 	navhq.do_now();     // refresh navigation controls
 	cal_set('calout');  // refresh/clear dungeon view
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
	calhq.url = dungeon_display_file+'?ajax=1&newmap';
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

function list_set(which) {
	calhq.url = 'ajax_list.php?offset=0&range=400';
	calhq.div = "calout";
	calhq.do_hq();
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

function class_hq(param) {
	/*  this is a 'class' to wrap around http_request  */

	/*  consider adding .prototype?
	    http://javascript.about.com/library/bltut35.htm  */

	/*  'constructor' logic goes here  */
	this.div = param;
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

