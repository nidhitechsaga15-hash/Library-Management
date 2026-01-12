import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createStackNavigator } from '@react-navigation/stack';
import { Ionicons } from '@expo/vector-icons';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet, Alert } from 'react-native';

// Student Screens
import StudentDashboard from '../screens/dashboards/StudentDashboard';
import BooksScreen from '../screens/student/books/BooksScreen';
import MyBooksScreen from '../screens/student/my-books/MyBooksScreen';
import FinesScreen from '../screens/student/fines/FinesScreen';
import LibraryCardScreen from '../screens/student/library-card/LibraryCardScreen';
import ReservationsScreen from '../screens/student/reservations/ReservationsScreen';
import ChatScreen from '../screens/shared/chat/ChatScreen';
import NotificationsScreen from '../screens/shared/notifications/NotificationsScreen';
import ProfileScreen from '../screens/shared/profile/ProfileScreen';

const Tab = createBottomTabNavigator();
const Stack = createStackNavigator();

function StudentTabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#4299e1',
        tabBarInactiveTintColor: '#666',
        headerShown: false,
      }}
    >
      <Tab.Screen
        name="Dashboard"
        component={StudentDashboard}
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
        name="MyBooks"
        component={MyBooksScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="library" size={size} color={color} />
          ),
          title: 'My Books',
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
        component={StudentMoreStack}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="menu" size={size} color={color} />
          ),
        }}
      />
    </Tab.Navigator>
  );
}

function StudentMoreStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: '#4299e1',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    >
      <Stack.Screen name="MoreMenu" component={StudentMoreMenu} options={{ title: 'More' }} />
      <Stack.Screen name="Fines" component={FinesScreen} />
      <Stack.Screen name="LibraryCard" component={LibraryCardScreen} />
      <Stack.Screen name="Reservations" component={ReservationsScreen} />
      <Stack.Screen name="Notifications" component={NotificationsScreen} />
      <Stack.Screen name="Profile" component={ProfileScreen} />
    </Stack.Navigator>
  );
}

function StudentMoreMenu({ navigation }) {
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
          onPress={() => handleNavigate('Fines')}
          activeOpacity={0.7}
        >
          <Ionicons name="cash" size={24} color="#4299e1" />
          <Text style={styles.menuText}>Fine History</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('LibraryCard')}
          activeOpacity={0.7}
        >
          <Ionicons name="card" size={24} color="#4299e1" />
          <Text style={styles.menuText}>Library Card</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Reservations')}
          activeOpacity={0.7}
        >
          <Ionicons name="time" size={24} color="#4299e1" />
          <Text style={styles.menuText}>My Reservations</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Notifications')}
          activeOpacity={0.7}
        >
          <Ionicons name="notifications" size={24} color="#4299e1" />
          <Text style={styles.menuText}>Notifications</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Profile')}
          activeOpacity={0.7}
        >
          <Ionicons name="person-circle" size={24} color="#4299e1" />
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

export default StudentTabs;

