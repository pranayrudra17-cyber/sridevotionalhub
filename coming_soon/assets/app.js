

// countdown
(function () {
    const second = 1000,
          minute = second * 60,
          hour = minute * 60,
          day = hour * 24;
  
    //I'm adding this section so I don't have to keep updating this pen every year :-)
    //remove this if you don't need it
    let today = new Date(),
        dd = String(today.getDate()).padStart(2, "0"),
        mm = String(today.getMonth() + 1).padStart(2, "0"),
        yyyy = today.getFullYear(),
        nextYear = yyyy + 1,
        dayMonth = "08/24/",
        birthday = dayMonth + yyyy;
    
    today = mm + "/" + dd + "/" + yyyy;
    if (today > birthday) {
      birthday = dayMonth + nextYear;
    }
    //end
    
    const countDown = new Date(birthday).getTime(),
        x = setInterval(function() {    
  
          const now = new Date().getTime(),
                distance = countDown - now;
  
          if(document.getElementById("days"))document.getElementById("days").innerText = Math.floor(distance / (day))
            if(document.getElementById("hours"))document.getElementById("hours").innerText = Math.floor((distance % (day)) / (hour))
           if(document.getElementById("minutes"))document.getElementById("minutes").innerText = Math.floor((distance % (hour)) / (minute))
           if(document.getElementById("seconds"))document.getElementById("seconds").innerText = Math.floor((distance % (minute)) / second)
  
          //do something later when date is reached
          if (distance < 0) {
            document.getElementById("headline").innerText = "Welcome to Sri Devotional Hub!";
            document.getElementById("countdown").style.display = "none";
            document.getElementById("content").style.display = "block";
            clearInterval(x);
          }
          //seconds
        }, 0)
    }());