// Add to Cart Function
function addToCart(productId) {
    fetch('/Groceryproject/includes/functions.php?action=add_to_cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Product added to cart!');
            updateCartCount(data.count);
        } else if(data.error) {
            alert(data.error);
            if(data.redirect) {
                window.location.href = data.redirect;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart. Please try again.');
    });
}

// Add to Wishlist
function addToWishlist(productId) {
    fetch('/Groceryproject/includes/functions.php?action=add_to_wishlist', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Added to wishlist!');
            updateWishlistCount(data.count);
        } else if(data.error) {
            alert(data.error);
            if(data.redirect) {
                window.location.href = data.redirect;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to wishlist. Please try again.');
    });
}

// Search Products with Autocomplete
function searchProducts(query) {
    if(query.length > 2) {
        fetch('/Groceryproject/search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(products => {
                let resultsDiv = document.getElementById('searchResults');
                if(!resultsDiv) return;
                
                resultsDiv.innerHTML = '';
                
                if(products.length === 0) {
                    resultsDiv.innerHTML = '<div class="no-results">No products found</div>';
                    return;
                }
                
                products.forEach(product => {
                    let div = document.createElement('div');
                    div.className = 'search-result-item';
                    div.innerHTML = `
                        <a href="/Groceryproject/product.php?id=${product.id}">
                            <img src="assets/images/${product.image}" width="50" height="50">
                            <span>${product.name} - $${product.price}</span>
                        </a>
                    `;
                    resultsDiv.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    } else {
        let resultsDiv = document.getElementById('searchResults');
        if(resultsDiv) {
            resultsDiv.innerHTML = '';
        }
    }
}

// Update Cart Count
function updateCartCount(count) {
    let cartCount = document.querySelector('.cart-count');
    if(cartCount) {
        cartCount.textContent = count;
    }
}

// Update Wishlist Count
function updateWishlistCount(count) {
    let wishlistCount = document.querySelector('.wishlist-count');
    if(wishlistCount) {
        wishlistCount.textContent = count;
    }
}

// Update Quantity in Cart
function updateQuantity(cartId, quantity) {
    if(quantity < 1) {
        if(confirm('Remove item from cart?')) {
            removeFromCart(cartId);
        }
        return;
    }
    
    fetch('/Groceryproject/includes/functions.php?action=update_quantity', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_id=' + cartId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload(); // Reload page to update totals
        }
    });
}

// Remove from Cart
function removeFromCart(cartId) {
    if(confirm('Are you sure you want to remove this item?')) {
        fetch('/Groceryproject/includes/functions.php?action=remove_from_cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cart_id=' + cartId
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        let searchResults = document.getElementById('searchResults');
        let searchInput = document.getElementById('searchInput');
        
        if(searchResults && searchInput && 
           !searchResults.contains(e.target) && 
           !searchInput.contains(e.target)) {
            searchResults.innerHTML = '';
        }
    });
});