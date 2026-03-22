<div class='product-card'>
    <img src='assets/images/<?php echo $product['image']; ?>' alt='<?php echo $product['name']; ?>'>
    <h3><?php echo $product['name']; ?></h3>
    <p class='price'>$<?php echo $product['price']; ?></p>
    <div class='product-actions'>
        <button onclick='addToCart(<?php echo $product['id']; ?>)'>Add to Cart</button>
        <button onclick='addToWishlist(<?php echo $product['id']; ?>)'>❤️</button>
    </div>
</div>