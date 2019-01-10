@extends('layouts.app')

@section('content')
<style>
    @import url(http://fonts.googleapis.com/css?family=Ubuntu);
    body{
      background-color: black;
      color: white;
      font-family: 'Ubuntu', sans-serif;
      font-size: 16px;
      line-height: 20px;  
    }
    h4{
      font-size: 18px;
      line-height: 22px;
      color: #353535;
    }
    #log {
      position: relative;
      top: -34px;
    }
    #scrollLock{
      width:2px;
      height: 2px;
      overflow:visible;
    }
  </style>
<div class="container">
    <div class="row justify-content-center">
       @php $interval = 1000; $textColor = "white"; @endphp
       @if($interval < 100)  $interval = 100; @endif
          @if(isset($log_file_url))
            {{file_get_contents($log_file_url)}}
          @else
            <script>
              setInterval(readLogFile, <?php echo $interval; ?>);
              window.onload = readLogFile; 
              var pathname = window.location.pathname;
              var scrollLock = true;
              
              $(document).ready(function(){
                $('.disableScrollLock').click(function(){
                  $("html,body").clearQueue()
                  $(".disableScrollLock").hide();
                  $(".enableScrollLock").show();
                  scrollLock = false;
                });
                $('.enableScrollLock').click(function(){
                  $("html,body").clearQueue()
                  $(".enableScrollLock").hide();
                  $(".disableScrollLock").show();
                  scrollLock = true;
                });
              });
              function readLogFile(){
                $.get(pathname, { getLog : "true" }, function(data) {
                  data = data.replace(new RegExp("\n", "g"), "<br />");
                      $("#log").html(data);
                      if(scrollLock == true) { $('html,body').animate({scrollTop: $("#scrollLock").offset().top}, <?php echo $interval; ?>) };
                  });
              }
            </script>
              <h4><?php echo $log_file_url; ?></h4>
              <div id="log">
                
              </div>
              <div id="scrollLock"> <input class="disableScrollLock" type="button" value="Disable Scroll Lock" /> <input class="enableScrollLock" style="display: none;" type="button" value="Enable Scroll Lock" /></div>
            @endif  
</div>
</div>
@endsection
