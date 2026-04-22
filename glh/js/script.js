let userBox = document.querySelector('.header .header-2 .user-box');

document.querySelector('#user-btn').onclick = () =>{
   userBox.classList.toggle('active');
   navbar.classList.remove('active');
}

let navbar = document.querySelector('.header .header-2 .navbar');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   userBox.classList.remove('active');
}

window.onscroll = () =>{
   userBox.classList.remove('active');
   navbar.classList.remove('active');

   if(window.scrollY > 60){
      document.querySelector('.header .header-2').classList.add('active');
   }else{
      document.querySelector('.header .header-2').classList.remove('active');
   }
}

document.querySelectorAll('.change-qty').forEach(button => {
   button.addEventListener('click', () => {
      const wrapper = button.closest('.quantity-picker');
      if (!wrapper) return;
      const input = wrapper.querySelector('.qty');
      if (!input || input.disabled) return;
      const change = Number(button.dataset.change || 0);
      let value = Number(input.value) + change;
      if (value < 1) value = 1;
      input.value = value;
   });
});

document.querySelectorAll('.change-qty-cart').forEach(button => {
   button.addEventListener('click', (e) => {
      e.preventDefault();
      const wrapper = button.closest('.quantity-picker');
      if (!wrapper) return;
      const input = wrapper.querySelector('.qty');
      if (!input) return;
      const change = Number(button.dataset.change || 0);
      let value = Number(input.value) + change;
      if (value < 1) value = 1;
      input.value = value;
      
      // Auto-submit the form
      const form = wrapper.closest('form');
      if (form) {
         form.querySelector('input[name="update_cart"]').click();
      }
   });
});