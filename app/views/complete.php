<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel PHP Framework</title>
    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:300,400,700);

        body {
            margin:0;
            font-family:'Lato', sans-serif;
            color: #999;
        }

        a, a:visited {
            color:#FF5949;
            text-decoration:none;
        }

        a:hover {
            text-decoration:underline;
        }

        ul li {
            display:inline;
            margin:0 1.2em;
        }

        p {
            margin:2em 0;
            color:#555;
        }
    </style>
</head>
<body>
    <div class="formw">
        <h5>Uploaded! Do another?</h5>
        <form method="post" action="/" enctype="multipart/form-data">
            <input type="text" name="title" value="" placeholder="title" /><br />
            <input type="file" name="file" placeholder="upload image" /><br />
            <input type="submit"  name="submit" value="Submit" />
        </form>
    </div>
</body>
</html>