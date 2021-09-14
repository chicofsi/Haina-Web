// Change the type of input to password or text
function Toggle() {
  var temp = document.getElementById("password");
  if (temp.type === "password") {
    temp.type = "text";
  }
  else {
    temp.type = "password";
  }
}
