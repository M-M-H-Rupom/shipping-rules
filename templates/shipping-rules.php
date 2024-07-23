<?php
if (isset($_POST['shipping_rules_submit'])) {
    if(isset($_POST['row_count'])){
        update_option('row_count', $_POST['row_count']);
    }
    if(isset($_POST['shipping_fields'])){
        update_option('shipping_fields', $_POST['shipping_fields']);
    }
}
$shipping_rows = get_option( 'shipping_fields');
$row_count = get_option('row_count');
// print_r()
?>  
<div class="shp_rules_container">
    <form action="" method="POST">
        <input type="hidden" name="row_count" id="row_count" value="<?php echo $row_count; ?>">
        <table class="form-table" id="shipping_rules_table">
            <tr>
                <th>Based on Shipping</th>
                <th>Select One</th>
                <th>Priority</th>
                <th>Shipping Amount</th>
            </tr>
            <?php 
            if(!empty($shipping_rows)){
                foreach($shipping_rows as $row_key => $row_value){
                    // print_r($row_value);
                ?>  
                <tr class="shipping_row" data-row-count="<?php echo $row_key ?>">
                    <td>
                        <div class="select_shipping">
                            <select name="shipping_fields[<?php echo $row_key ?>][select_shipping]" id="shipping_fields_<?php echo $row_count ?>_select_shipping" class='shipping_type'>
                                <option value="">Select shipping</option>
                                <option value="product_shipping" <?php  echo selected($row_value['select_shipping'] , 'product_shipping') ?>>Product shipping</option>
                                <option value="category_shipping" <?php  echo selected($row_value['select_shipping'] , 'category_shipping') ?>>Category shipping</option>
                                <option value="cart_amount_shipping" <?php  echo selected($row_value['select_shipping'] , 'cart_amount_shipping') ?>>Cart amount shipping</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="select_one">
                            <!-- product data here -->
                            <?php 
                            $products = wc_get_products(array(
                                'limit' => -1,
                                'status' => 'publish'
                            ));
                            
                            $product_list = '';
                            foreach ($products as $product) {
                                $selected = selected($row_value['shipping_item']['product_shipping'], $product->get_id(),false);
                                $product_list .= '<option value="' . $product->get_id() . '"' . $selected . '>' . $product->get_name() . '</option>';
                            }
                            ?>
                            <div class="select_product">
                                <select name="shipping_fields[<?php echo $row_key ?>][shipping_item][product_shipping]" id="shipping_fields_<?php echo $row_count ?>_select_product">
                                    <option value="">Select Product</option>
                                    <?php echo $product_list; ?>
                                </select>
                            </div>
                            <!-- cls -->
                            <!-- category data here -->
                            <?php
                            $categories = get_terms(array(
                                            'taxonomy' => 'product_cat',
                                            'hide_empty' => false
                                        ));
                            $category_list = '';
                            foreach ($categories as $category) {
                                $selected = selected($row_value['shipping_item']['category_shipping'], $category->term_id,false);
                                $category_list .= '<option value="' . $category->term_id . '"' . $selected . '>' . $category->name . '</option>';
                            }
                            ?>
                            <div class="select_category">
                                <select name="shipping_fields[<?php echo $row_key ?>][shipping_item][category_shipping]" id="shipping_fields_<?php echo $row_count ?>_select_category">
                                    <option value="">Select Category</option>
                                    <?php echo $category_list; ?>
                                </select>
                            </div>
                            <!-- cls -->
                            <!-- Cart amount here  -->
                            <div class="min_max_cart_amount">
                                <span>
                                    <input type="text" name="shipping_fields[<?php echo $row_key ?>][shipping_item][cart_amount_shipping]" id="" placeholder="Max Card Amount" value="<?php echo $row_value['shipping_item']['cart_amount_shipping'] ?>">
                                </span>
                            </div>
                            <!-- cls -->
                        </div>
                    </td>
                    <td class="shipping_priority">
                        <div class="shipping_priority_div">
                            <span>
                                <input type="number" step="1" name="shipping_fields[<?php echo $row_key ?>][shipping_priority]" id="" placeholder="Priority" value="<?php echo $row_value['shipping_priority']?>">
                            </span>
                        </div>
                    </td>
                    <td class="shipping_amount_with_close_btn">
                        <div class="shipping_amount">
                            <input type="text" name="shipping_fields[<?php echo $row_key ?>][shipping]" id="" placeholder="Enter amount" value="<?php echo $row_value['shipping'] ?>">
                        </div>
                        <div class="shp_close_btn">
                            <span><i class="fas fa-xmark"></i></span>
                        </div>
                    </td>
                </tr>
                <?php
                }
            }else{    // if row empty
                ?>
                <tr class="shipping_row" data-row-count="<?php  ?>">
                    <td>
                        <div class="select_shipping">
                            <select name="shipping_fields[<?php echo $row_count ?>][select_shipping]" id="shipping_fields_<?php echo $row_count ?>_select_shipping" class='shipping_type'>
                                <option value="">Select shipping</option>
                                <option value="product_shipping" >Product shipping</option>
                                <option value="category_shipping">Category shipping</option>
                                <option value="cart_amount_shipping">Cart amount shipping</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="select_one">
                            <!-- product data here -->
                            <?php 
                            $products = wc_get_products(array(
                                'limit' => -1,
                                'status' => 'publish'
                            ));
                            
                            $product_list = '';
                            foreach ($products as $product) {
                                $product_list .= '<option value="' . $product->get_id() . '">' . $product->get_name() . '</option>';
                            }
                            ?>
                            <div class="select_product">
                                <select name="shipping_fields[<?php echo $row_count ?>][shipping_item][product_shipping]" id="shipping_fields_<?php echo $row_count ?>_select_product">
                                    <option value="">Select Product</option>
                                    <?php echo $product_list; ?>
                                </select>
                            </div>
                            <!-- cls -->
                            <!-- category data here -->
                            <?php
                            $categories = get_terms(array(
                                            'taxonomy' => 'product_cat',
                                            'hide_empty' => false
                                        ));
                            $category_list = '';
                            foreach ($categories as $category) {
                                $category_list .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                            }
                            ?>
                            <div class="select_category">
                                <select name="shipping_fields[<?php echo $row_count ?>][shipping_item][category_shipping]" id="shipping_fields_<?php echo $row_count ?>_select_category">
                                    <option value="">Select Category</option>
                                    <?php echo $category_list; ?>
                                </select>
                            </div>
                            <!-- cls -->
                            <!-- Cart amount here  -->
                            <div class="min_max_cart_amount">
                                <span>
                                    <input type="text" name="shipping_fields[<?php echo $row_count ?>][shipping_item][cart_amount_shipping]" id="" placeholder="Max Card Amount" value="">
                                </span>
                            </div>
                            <!-- cls -->
                        </div>
                    </td>
                    <!-- shipping priority -->
                    <td class="shipping_priority">
                        <div class="shipping_priority_div">
                            <span>
                                <input type="text" name="shipping_fields[<?php echo $row_count ?>][shipping_priority]" id="" placeholder="Priority" value="">
                            </span>
                        </div>
                    </td>
                    <!-- cls -->
                    <td class="shipping_amount_with_close_btn">
                        <div class="shipping_amount">
                            <input type="text" name="shipping_fields[<?php echo $row_count ?>][shipping]" id="" placeholder="Enter amount" value="">
                        </div>
                        <div class="shp_close_btn">
                            <span><i class="fas fa-xmark"></i></span>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <div class="add_more_shipping_row">
            <span>Add more </span>
        </div>
        <div class="save_btn">
            <input type="submit" value="Save Changes" name="shipping_rules_submit">
        </div>
    </form>
</div>
<?php