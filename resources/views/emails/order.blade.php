<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ایمیل</title>
</head>
<body
    style="
      font-family: Tahoma, Arial, 'Microsoft Sans Serif', sans-serif;
      background-color: #f2f2f2;
      margin: 0;
      padding: 0;
      direction: rtl;
      text-align: right;
      line-height: 1.5;
      font-size: 16px;
    "
>
<div style="width: 90%; max-width: 800px; padding: 15px; margin: 0 auto">
    <div style="text-align: center; padding: 15px 0">
        <div style="display: inline-block">

        </div>
    </div>

    <div
        style="
          background-color: #ffffff;
          border-radius: 15px;
          padding: 20px;
          min-height: 60svh;
          margin-bottom: 20px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        "
    >


        <div>
            {!! $content !!}

        </div>

    </div>

    <div style="text-align: center; padding: 10px 0">
        <div style="text-align: center; margin-bottom: 20px">
            <a
                href="{{ url('/') }}"
                target="_blank"
                style="
              color: #333;
              text-decoration: none;
              font-size: 14px;
              mso-text-raise: 2px;
              padding-top: 5px;
              padding-bottom: 5px;
              padding-left: 15px;
            "
            >Home</a
            >
        <div style="text-align: center">

        </div>
    </div>
</div>
</body>
</html>
