import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createStackNavigator } from '@react-navigation/stack';
import { Ionicons } from '@expo/vector-icons';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet, Alert } from 'react-native';

// Staff Screens
import StaffDashboard from '../screens/dashboards/StaffDashboard';
import BooksScreen from '../screens/staff/books/BooksScreen';
import StudentsScreen from '../screens/staff/students/StudentsScreen';
import BorrowsScreen from '../screens/staff/borrows/BorrowsScreen';
import FinesScreen from '../screens/staff/fines/FinesScreen';
import BookRequestsScreen from '../screens/staff/book-requests/BookRequestsScreen';
import ScannerScreen from '../screens/staff/scanner/ScannerScreen';
import LibraryCardsScreen from '../screens/staff/library-cards/LibraryCardsScreen';
import ChatScreen from '../screens/shared/chat/ChatScreen';
import NotificationsScreen from '../screens/shared/notifications/NotificationsScreen';
import ProfileScreen from '../screens/shared/profile/ProfileScreen';

const Tab = createBottomTabNavigator();
const Stack = createStackNavigator();

function StaffTabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#48bb78',
        tabBarInactiveTintColor: '#666',
        headerShown: false,
      }}
    >
      <Tab.Screen
        name="Dashboard"
        component={StaffDashboard}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="home" size={size} color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="Books"
        component={BooksScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="book" size={size} color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="Issue/Return"
        component={BorrowsScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="swap-horizontal" size={size} color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="Chat"
        component={ChatScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="chatbubbles" size={size} color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="More"
        component={StaffMoreStack}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="menu" size={size} color={color} />
          ),
        }}
      />
    </Tab.Navigator>
  );
}

function StaffMoreStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: '#48bb78',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    >
      <Stack.Screen name="MoreMenu" component={StaffMoreMenu} options={{ title: 'More' }} />
      <Stack.Screen name="Students" component={StudentsScreen} />
      <Stack.Screen name="Fines" component={FinesScreen} />
      <Stack.Screen name="BookRequests" component={BookRequestsScreen} />
      <Stack.Screen name="Scanner" component={ScannerScreen} />
      <Stack.Screen name="LibraryCards" component={LibraryCardsScreen} />
      <Stack.Screen name="Notifications" component={NotificationsScreen} />
      <Stack.Screen name="Profile" component={ProfileScreen} />
    </Stack.Navigator>
  );
}

function StaffMoreMenu({ navigation }) {
  const handleNavigate = (screenName) => {
    try {
      navigation.navigate(screenName);
    } catch (error) {
      console.error('Navigation error:', error);
      Alert.alert('Error', `Failed to navigate to ${screenName}`);
    }
  };

  return (
    <View style={styles.container}>
      <ScrollView>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Students')}
          activeOpacity={0.7}
        >
          <Ionicons name="people" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Students</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Fines')}
          activeOpacity={0.7}
        >
          <Ionicons name="cash" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Fines</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('BookRequests')}
          activeOpacity={0.7}
        >
          <Ionicons name="hand-left" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Book Requests</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Scanner')}
          activeOpacity={0.7}
        >
          <Ionicons name="qr-code" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Barcode Scanner</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('LibraryCards')}
          activeOpacity={0.7}
        >
          <Ionicons name="card" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Library Cards</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Notifications')}
          activeOpacity={0.7}
        >
          <Ionicons name="notifications" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Notifications</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Profile')}
          activeOpacity={0.7}
        >
          <Ionicons name="person-circle" size={24} color="#48bb78" />
          <Text style={styles.menuText}>Profile</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
    minHeight: 56,
  },
  menuText: {
    marginLeft: 15,
    fontSize: 16,
    color: '#333',
    flex: 1,
  },
  chevron: {
    marginLeft: 'auto',
  },
});

export default StaffTabs;

