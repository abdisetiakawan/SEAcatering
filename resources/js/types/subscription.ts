export interface Subscription {
    id: number;
    subscription_number: string;
    meals_per_day: number;
    meal_plan: {
        id: number;
        name: string;
        type: string;
        price_per_meal: number;
        image: string;
    };
    delivery_address: {
        id: number;
        address_line_1: string;
        city: string;
        province: string;
    };
    start_date: string;
    end_date: string;
    status: string;
    frequency: string;
    delivery_days: string[];
    delivery_time_preference: string;
    price_per_meal: number;
    total_price: number;
    discount_amount: number;
    next_delivery_date: string;
    created_at: string;
    delivery_frequency: string;
    preferred_delivery_time: string;
    delivery_address_id: number;
    latest_order: Order | null;
}

interface Order {
    id: number;
    order_number: string;
    delivery_date: string;
    status: string;
    payment_status: string;
    total_amount: number;
    can_pay: boolean;
}
