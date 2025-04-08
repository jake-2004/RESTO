<style>
  /* Notification styles */
  .notification-link {
    position: relative;
    display: inline-block;
    margin-right: 15px;
    color: #ffffff;
  }
  
  .notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #ff4757;
    color: white;
    border-radius: 50%;
    padding: 0.25em 0.6em;
    font-size: 10px;
    font-weight: bold;
  }
  
  .notification-dropdown {
    width: 300px;
    padding: 0;
    max-height: 400px;
    overflow-y: auto;
    background-color: #fff;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    border: none;
  }
  
  .notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    background-color: #f8f9fa;
  }
  
  .notification-header a {
    color: #007bff;
    font-size: 12px;
  }
  
  .notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .notification-item:hover {
    background-color: #f8f9fa;
  }
  
  .notification-item.unread {
    background-color: #f0f7ff;
  }
  
  .notification-item .time {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
  }
  
  .notification-empty {
    padding: 15px;
    text-align: center;
    color: #999;
  }
</style> 