const spanBtn = document.getElementsByClassName('product-btn');

ajaxBtn(spanBtn);



function ajaxBtn(spanBtn) {
    for (i = 0; i < spanBtn.length; i++) {
        spanBtn[i].addEventListener('click', function (e) {
            e.preventDefault();

            const action = e.target.attributes.id.value.split('-')[0]
            const id = e.target.attributes.id.value.split('-')[1]

            $.post(
                '/ajaxAdd',
                {'action': action, 'id': id},
                function (data) {
                    let quantity = document.getElementById('product-qty-' + id).innerHTML;

                    switch (action) {
                        case 'add':
                            let addQty = parseInt(quantity) + 1
                            document.getElementById('product-qty-' + id).innerHTML = addQty;
                            break;
                        case 'sub':
                            if(quantity > 0) {
                                let subQty = parseInt(quantity) - 1
                                document.getElementById('product-qty-' + id).innerHTML = subQty;
                            }
                            break;
                        default:
                            break;
                    }

                    document.getElementById('cart-display').innerHTML = "";
                    document.getElementById('cart-display').innerHTML = data;

                    // TO DO Activer les boutons du panier
                    // ajaxBtn(spanBtn);
                }
            )
        })
    }
}

