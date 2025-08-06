<?php
/**
 * Order Assignment System
 * Automatically assigns orders to available drivers
 */
class OrderAssignment {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Assign order to available driver
     */
    public function assignOrderToDriver($order_id) {
        // Get available drivers
        $available_drivers = $this->getAvailableDrivers();
        
        if (empty($available_drivers)) {
            return [
                'success' => false,
                'message' => 'No available drivers at the moment. Your order will be assigned when a driver becomes available.'
            ];
        }
        
        // Select the best driver (closest to restaurant or least busy)
        $selected_driver = $this->selectBestDriver($available_drivers, $order_id);
        
        if (!$selected_driver) {
            return [
                'success' => false,
                'message' => 'Unable to assign driver at this time.'
            ];
        }
        
        // Update order with driver assignment
        $update_order = "UPDATE users_orders SET driver_id = ?, estimated_delivery_time = ? WHERE o_id = ?";
        $stmt = $this->db->prepare($update_order);
        
        // Calculate estimated delivery time (30 minutes from now)
        $estimated_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $stmt->bind_param("isi", $selected_driver['id'], $estimated_time, $order_id);
        
        if (!$stmt->execute()) {
            return [
                'success' => false,
                'message' => 'Failed to assign driver to order.'
            ];
        }
        
        // Create delivery tracking record
        $tracking_result = $this->createDeliveryTracking($order_id, $selected_driver['id']);
        
        if (!$tracking_result) {
            return [
                'success' => false,
                'message' => 'Order assigned but tracking failed to initialize.'
            ];
        }
        
        // Update driver status to busy
        $this->updateDriverStatus($selected_driver['id'], 'busy');
        
        return [
            'success' => true,
            'message' => 'Order assigned to driver: ' . $selected_driver['name'],
            'driver_id' => $selected_driver['id'],
            'driver_name' => $selected_driver['name'],
            'driver_phone' => $selected_driver['phone'],
            'estimated_delivery_time' => $estimated_time
        ];
    }
    
    /**
     * Get available drivers
     */
    private function getAvailableDrivers() {
        $query = "SELECT id, name, phone, vehicle_type, current_latitude, current_longitude 
                  FROM drivers 
                  WHERE status = 'available' 
                  ORDER BY created_at ASC";
        
        $result = $this->db->query($query);
        $drivers = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
        }
        
        return $drivers;
    }
    
    /**
     * Select the best driver for the order
     */
    private function selectBestDriver($drivers, $order_id) {
        // Get order details
        $order_query = "SELECT customer_latitude, customer_longitude, delivery_address 
                       FROM users_orders WHERE o_id = ?";
        $stmt = $this->db->prepare($order_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_result = $stmt->get_result();
        $order = $order_result->fetch_assoc();
        
        if (!$order) {
            return null;
        }
        
        // For now, select the first available driver
        // In a more sophisticated system, you could:
        // 1. Calculate distance from driver to customer
        // 2. Consider driver's current workload
        // 3. Consider driver's rating
        // 4. Consider driver's vehicle type vs order size
        
        return $drivers[0];
    }
    
    /**
     * Create delivery tracking record
     */
    private function createDeliveryTracking($order_id, $driver_id) {
        $query = "INSERT INTO delivery_tracking (order_id, driver_id, status, created_at) 
                  VALUES (?, ?, 'assigned', NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $order_id, $driver_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update driver status
     */
    private function updateDriverStatus($driver_id, $status) {
        $query = "UPDATE drivers SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $driver_id);
        
        return $stmt->execute();
    }
    
    /**
     * Process pending orders (to be called by admin or cron job)
     */
    public function processPendingOrders() {
        $query = "SELECT o_id FROM users_orders 
                  WHERE driver_id IS NULL 
                  AND status IS NULL 
                  ORDER BY date ASC";
        
        $result = $this->db->query($query);
        $processed = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $assignment_result = $this->assignOrderToDriver($row['o_id']);
                if ($assignment_result['success']) {
                    $processed++;
                }
            }
        }
        
        return $processed;
    }
    
    /**
     * Get order assignment status
     */
    public function getOrderAssignmentStatus($order_id) {
        $query = "SELECT uo.o_id, uo.title, uo.driver_id, uo.estimated_delivery_time,
                         d.name as driver_name, d.phone as driver_phone,
                         dt.status as delivery_status
                  FROM users_orders uo
                  LEFT JOIN drivers d ON uo.driver_id = d.id
                  LEFT JOIN delivery_tracking dt ON uo.o_id = dt.order_id
                  WHERE uo.o_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Send notification to driver (placeholder for SMS/email)
     */
    public function notifyDriver($driver_id, $order_id) {
        // Get driver and order details
        $query = "SELECT d.name, d.phone, uo.title, uo.delivery_address
                  FROM drivers d
                  JOIN users_orders uo ON uo.driver_id = d.id
                  WHERE d.id = ? AND uo.o_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $driver_id, $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            // Here you would integrate with SMS/email service
            // For now, we'll just log the notification
            $message = "New order assigned to driver {$data['name']} ({$data['phone']}): {$data['title']} to {$data['delivery_address']}";
            error_log($message);
            
            return true;
        }
        
        return false;
    }
}
?> 