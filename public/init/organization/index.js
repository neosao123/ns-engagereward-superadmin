console.log("qawdsefdrgyjgfdsaSDfgh");
$(document).ready(function () {
  $(document).on("change", "#file", function () {
    alert();
    var filePath = $(this).val();
    var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
    if (!allowedExtensions.exec(filePath)) {
      toastr.error("Invalid File type", "Special Offer", {
        progressBar: false,
      });
      $(this).val(null);
      return false;
    } else {
      const file = this.files[0];
      if (file) {
        let reader = new FileReader();
        reader.onload = function (event) {
          $("#showImage").attr("src", event.target.result);
        };
        reader.readAsDataURL(file);
      }
    }
  });
});
