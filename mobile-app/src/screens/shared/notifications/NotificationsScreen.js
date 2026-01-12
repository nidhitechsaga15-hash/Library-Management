import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';

export default function NotificationsScreen({ navigation }) {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadNotifications();
  }, []);

  const loadNotifications = async () => {
    try {
      const response = await apiService.notifications.getAll();
      // Handle both array and object response
      const notifications = Array.isArray(response) ? response : (response.notifications || []);
      setNotifications(notifications);
    } catch (error) {
      console.error('Error loading notifications:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadNotifications();
  };

  const handleNotificationPress = async (notification) => {
    try {
      // Mark as read first
      if (!notification.is_read) {
        await apiService.notifications.markAsRead(notification.id);
      }
      
      // Navigate based on link
      if (notification.link && notification.link !== '#') {
        navigateFromLink(notification.link);
      } else {
        // Just refresh if no link
        loadNotifications();
      }
    } catch (error) {
      console.error('Error handling notification:', error);
    }
  };

  const navigateFromLink = (link) => {
    try {
      console.log('Navigating from notification link:', link);
      
      // Get root navigator (Tab Navigator) - go up 2 levels from More stack
      const rootNavigator = navigation.getParent()?.getParent();
      
      // Book detail pages - BookDetails is in More stack
      if (link.includes('/books/')) {
        const bookIdMatch = link.match(/\/books\/(\d+)/);
        if (bookIdMatch) {
          const bookId = parseInt(bookIdMatch[1]);
          navigation.navigate('BookDetails', { bookId });
          return;
        }
      }
      
      // Tab screens - navigate using root navigator
      if (link.includes('/borrows') && !link.includes('/book-requests')) {
        if (rootNavigator) {
          rootNavigator.navigate('Borrows');
        } else {
          // Fallback: try to navigate after going back
          navigation.goBack();
          setTimeout(() => {
            navigation.getParent()?.getParent()?.navigate('Borrows');
          }, 100);
        }
        return;
      }
      
      if (link.includes('/chat')) {
        if (rootNavigator) {
          rootNavigator.navigate('Chat');
        } else {
          navigation.goBack();
          setTimeout(() => {
            navigation.getParent()?.getParent()?.navigate('Chat');
          }, 100);
        }
        return;
      }
      
      if ((link.includes('/books') || link.includes('/admin/books') || link.includes('/staff/books') || link.includes('/student/books')) && !link.includes('/books/')) {
        if (rootNavigator) {
          rootNavigator.navigate('Books');
        } else {
          navigation.goBack();
          setTimeout(() => {
            navigation.getParent()?.getParent()?.navigate('Books');
          }, 100);
        }
        return;
      }
      
      if (link.includes('/dashboard') || link.includes('/admin/dashboard') || link.includes('/staff/dashboard') || link.includes('/student/dashboard')) {
        if (rootNavigator) {
          rootNavigator.navigate('Dashboard');
        } else {
          navigation.goBack();
          setTimeout(() => {
            navigation.getParent()?.getParent()?.navigate('Dashboard');
          }, 100);
        }
        return;
      }
      
      // More stack screens - can navigate directly
      if (link.includes('/book-requests')) {
        navigation.navigate('BookRequests');
        return;
      }
      
      if (link.includes('/fines')) {
        navigation.navigate('Fines');
        return;
      }
      
      if (link.includes('/users') || link.includes('/students')) {
        navigation.navigate('Users');
        return;
      }
      
      if (link.includes('/reports')) {
        navigation.navigate('Reports');
        return;
      }
      
      // Default: just refresh notifications
      loadNotifications();
    } catch (error) {
      console.error('Error navigating from link:', error);
      console.error('Link was:', link);
      loadNotifications();
    }
  };

  const renderNotification = ({ item }) => (
    <TouchableOpacity
      style={[styles.notificationCard, !item.is_read && styles.unreadCard]}
      onPress={() => handleNotificationPress(item)}
    >
      <View style={styles.notificationContent}>
        <Text style={styles.notificationTitle}>{item.title}</Text>
        <Text style={styles.notificationMessage}>{item.message}</Text>
        <Text style={styles.notificationTime}>
          {new Date(item.created_at).toLocaleString()}
        </Text>
      </View>
      {!item.is_read && <View style={styles.unreadDot} />}
    </TouchableOpacity>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notifications</Text>
        <View style={{ width: 24 }} />
      </View>
      <FlatList
        data={notifications}
        renderItem={renderNotification}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No notifications</Text>
          </View>
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#667eea',
    padding: 20,
    paddingTop: 50,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  listContent: {
    padding: 15,
  },
  notificationCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  unreadCard: {
    borderLeftWidth: 4,
    borderLeftColor: '#667eea',
  },
  notificationContent: {
    flex: 1,
  },
  notificationTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  notificationMessage: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  notificationTime: {
    fontSize: 12,
    color: '#999',
  },
  unreadDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#667eea',
    marginLeft: 10,
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
  },
});

