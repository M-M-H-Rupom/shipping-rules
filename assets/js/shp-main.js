;(function($){
    $.each( $('#shipping_rules_table tr'), function(key, row) {
        if( $(row).find('th').length == 0 ) {
            let item_inputs = $(row).find('td').eq(1)
            $.each(item_inputs.find('select, input'), function( key, input ) {
                if( $(input).val() == "" ) {
                    $(input).closest('div').hide()
                }
            })
        }
    } )
    
    $(document).on('change','.shipping_type',function(){
        let shipping_type = $(this).val();
        if(shipping_type == ''){
            $(this).closest('tr').find('td').eq(1).find('.select_one').children().hide();
        }else{
            $.each( $(this).closest('tr').find('td').eq(1).find('select,input'), function(key, input) {
                if( $(input).attr('name').indexOf(shipping_type) != -1 ) {
                    $(input).closest('div').show()
                } else {
                    $(input).closest('div').hide()
                    $(input).val("")
                }
            })
        }
        
    })
    $('.add_more_shipping_row span').on('click',function(){
        let row_count = $('#row_count').val()
        row_count++;
        // catch product
        let products_data = dsc_localize_data.products;
        let product_list = ''
        products_data.forEach((product) => {
            product_list += `<option value="${product.id}">${product.name}</option>`
        });
        // catch categories
        let categories_data = dsc_localize_data.categories;
        let category_list = ''
        categories_data.forEach((category) => {
            category_list += `<option value="${category.id}">${category.name}</option>`
        });
        // 
        let shp_row_data = `
            <tr class="shipping_row" data-row-count="${row_count}">
                <td>
                    <div class="select_shipping">
                        <select name="shipping_fields[${row_count}][select_shipping]" class='shipping_type'>
                            <option value="">Select shipping</option>
                            <option value="product_shipping">Product shipping</option>
                            <option value="category_shipping">Category shipping</option>
                            <option value="cart_amount_shipping">Cart amount shipping</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="select_one">
                        
                        <div class="select_product">
                            <select name="shipping_fields[${row_count}][shipping_item][product_shipping]" id="">
                                <option value="">Select Product</option>
                                ${product_list}
                            </select>
                        </div>
                        <!-- cls -->
                        <!-- category data here -->
                            
                        <div class="select_category">
                            <select name="shipping_fields[${row_count}][shipping_item][category_shipping]" id="">
                                <option value="">Select Category</option>
                                ${category_list}
                            </select>
                        </div>
                        <!-- cls -->
                        <!-- Cart amount here  -->
                        <div class="min_max_cart_amount">
                            <span>
                                <input type="text" name="shipping_fields[${row_count}][shipping_item][cart_amount_shipping]" id="" placeholder="Max Card Amount">
                            </span>
                        </div>
                        <!-- cls -->
                    </div>
                </td>
                <td class="shipping_priority">
                        <div class="shipping_priority_div">
                            <span>
                                <input type="text" name="shipping_fields[${row_count}][shipping_priority]" id="" placeholder="Priority" value="">
                            </span>
                        </div>
                    </td>
                <td class="shipping_amount_with_close_btn">
                    <div class="shipping_amount">
                        <input type="text" name="shipping_fields[${row_count}][shipping]" id="" placeholder="Enter amount" value="">
                    </div>
                    <div class="shp_close_btn">
                        <span><i class="fas fa-xmark"></i></span>
                    </div>
                </td>
            </tr>
        `
        // row_clone.find('input').val('');
        $('#row_count').val(row_count)
        $('#shipping_rules_table').append(shp_row_data);
        $('#shipping_rules_table .shipping_row').eq(-1).find('.select_one').children().hide();
    })

    $(document).on('click','.shp_close_btn',function(){
        if($('.shipping_row').length == 1){
            return;
        }
        $(this).closest('.shipping_row').remove();
    })
    
})(jQuery)
