<html>
<head>
<title>DIV XMLHTTP example</title>
</head>

<body>

<button id="mybutton">Make a request</button>

<button id="myreset">Reset</button>

<div id="mydiv" style="border: solid; margin: 1em;">Show this text when this page is first loaded</div>

<script type="text/javascript">

    var http_request = false;

    function makeRequest(url) {
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7...
            http_request = new XMLHttpRequest();
            }
        else if (window.ActiveXObject) { // IE6 and older
            http_request = new ActiveXObject("Microsoft.XMLHTTP");
            }
        http_request.onreadystatechange = alertContents;
        http_request.open('GET', url, true);
        http_request.send(null);
        }

    function alertContents() {
        if (http_request.readyState == 4) {
            if (http_request.status == 200) {
//              alert(http_request.responseText);
                document.getElementById('mydiv').innerHTML = http_request.responseText;
                }
            else {
                alert('There was a problem with the request.');
                }
            }
        }

    document.getElementById('mybutton').onclick = function() {
        makeRequest('get-rich-content.html');
        }
 
//  document.getElementById('mybutton').onmouseover = function() {
    document.getElementById('myreset').onclick = function() {
        document.getElementById('mydiv').innerHTML = 'Show this text on reset';
        }

</script>
</body>
</html>


