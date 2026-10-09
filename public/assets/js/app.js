function getCsrfToken() {
  return window.CSRF_TOKEN || $('meta[name="csrf-token"]').attr("content");
}

function postForm(url, data) {
  return $.ajax({
    url: url,
    method: "POST",
    dataType: "json",
    data: data,
  });
}

// Booking (AJAX) + Favorites (AJAX)
$(function () {
  // Reserve a room and proceed to payment checkout.
  $(document).on("click", ".book-btn", function (e) {
    if (window.isAuthenticated === false) {
      e.preventDefault();
      e.stopImmediatePropagation();
      Feedback.fire({
        icon: "warning",
        title: "Login Required",
        text: "You must log in first to rent a room.",
        confirmButtonText: "Go to Login",
        confirmButtonColor: "var(--dash-primary)",
      }).then((res) => {
        if (res.isConfirmed) {
          window.location.href = "/tenant/?url=auth/login";
        }
      });
      return;
    }

    const roomId = $(this).data("room-id");
    const bookingForm = $(this).closest(".booking-form");
    const startDate = bookingForm.find('input[name="start_date"]').val();
    const endDate = bookingForm.find('input[name="end_date"]').val();

    postForm(window.APP_BASE_URL + "booking/store", {
      room_id: roomId,
      start_date: startDate,
      end_date: endDate,
      csrf_token: getCsrfToken(),
    })
      .done(function (res) {
        if (res && res.ok && res.checkout_url) {
          window.location.href = res.checkout_url;
        } else {
          Feedback.fire(
            "Error",
            res && res.message ? res.message : "Unable to create booking",
            "error",
          );
        }
      })
      .fail(function (xhr) {
        let msg = "Unable to create booking";
        try {
          if (xhr.responseJSON && xhr.responseJSON.message)
            msg = xhr.responseJSON.message;
        } catch (e) {}
        Feedback.fire("Error", msg, "error");
      });
  });

  // Toggle favorite for a boarding house.
  $(document).on("click", ".favorite-toggle-btn", function (e) {
    if (window.isAuthenticated === false) {
      e.preventDefault();
      e.stopImmediatePropagation();
      Feedback.fire({
        icon: "warning",
        title: "Login Required",
        text: "You must log in first to add favorites.",
        confirmButtonText: "Go to Login",
        confirmButtonColor: "var(--dash-primary)",
      }).then((res) => {
        if (res.isConfirmed) {
          window.location.href = "/tenant/?url=auth/login";
        }
      });
      return;
    }

    const houseId = $(this).data("house-id");
    postForm(window.APP_BASE_URL + "favorites/toggle", {
      boarding_house_id: houseId,
      csrf_token: getCsrfToken(),
    })
      .done(function (res) {
        if (res && res.ok) {
          Feedback.fire(
            "Updated",
            res.favorited ? "Added to favorites" : "Removed from favorites",
            "success",
          );
        } else {
          Feedback.fire(
            "Error",
            res && res.message ? res.message : "Unable to update favorites",
            "error",
          );
        }
      })
      .fail(function (xhr) {
        let msg = "Unable to update favorites";
        try {
          if (xhr.responseJSON && xhr.responseJSON.message)
            msg = xhr.responseJSON.message;
        } catch (e) {}
        Feedback.fire("Error", msg, "error");
      });
  });
});
