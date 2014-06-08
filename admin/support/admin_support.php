<?php

function ois_support() {
  
  // Create a title
  ois_section_title('Product Support', 'We\'re here to help', '');
  // Create a quick little view
  ?>
  <div style="padding: 5px 10px">
    <h2>hello+os@skindustryhq.com</h2>
    <p>That's the address to call if you have any questions at all. Please feel free to contact us!</p>
    
    <?php
    //include_once dirname(__FILE__) . '/../support/FAQ.html';
    ?>
  </div>
  <?php
  ois_section_end();
} // ois_support()

?>