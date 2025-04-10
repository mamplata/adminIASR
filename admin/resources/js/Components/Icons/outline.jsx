// Extra icons

import { defineComponent } from 'vue'

export const MenuFoldLineRightIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                stroke="currentColor"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M12 6H20M12 12H20M4 18H20M4 6L8 9L4 12"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

export const MenuFoldLineLeftIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                stroke="currentColor"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M12 6H20M12 12H20M4 18H20M8 6L4 9L8 12"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

export const DashboardIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path d="M5.5 12.5C5.22386 12.5 5 12.7239 5 13C5 13.2761 5.22386 13.5 5.5 13.5H7C7.27614 13.5 7.5 13.2761 7.5 13C7.5 12.7239 7.27614 12.5 7 12.5H5.5Z" />
                <path d="M6.93935 7.93935C7.13462 7.74409 7.4512 7.74409 7.64646 7.93935L8.70712 9.00001C8.90238 9.19528 8.90238 9.51186 8.70712 9.70712C8.51186 9.90238 8.19528 9.90238 8.00001 9.70712L6.93935 8.64646C6.74409 8.4512 6.74409 8.13462 6.93935 7.93935Z" />
                <path d="M17 12.5C16.7239 12.5 16.5 12.7239 16.5 13C16.5 13.2761 16.7239 13.5 17 13.5H18.5C18.7761 13.5 19 13.2761 19 13C19 12.7239 18.7761 12.5 18.5 12.5H17Z" />
                <path d="M15.4394 10.0606C15.2441 9.86538 15.2441 9.5488 15.4394 9.35354L16.5 8.29288C16.6953 8.09762 17.0119 8.09762 17.2071 8.29288C17.4024 8.48814 17.4024 8.80472 17.2071 8.99999L16.1465 10.0606C15.9512 10.2559 15.6346 10.2559 15.4394 10.0606Z" />
                <path d="M12.9661 12.741C12.9882 12.8236 13 12.9104 13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.0896 12 12.1764 12.0118 12.259 12.0339L13 11.2929C13.1953 11.0976 13.5119 11.0976 13.7071 11.2929C13.9024 11.4881 13.9024 11.8047 13.7071 12L12.9661 12.741Z" />
                <path d="M12 8.5C11.7239 8.5 11.5 8.27614 11.5 8V6.5C11.5 6.22386 11.7239 6 12 6C12.2761 6 12.5 6.22386 12.5 6.5V8C12.5 8.27614 12.2761 8.5 12 8.5Z" />
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M12 3C9.34784 3 6.8043 4.05357 4.92893 5.92893C3.05357 7.8043 2 10.3478 2 13C2 14.3132 2.25866 15.6136 2.7612 16.8268C3.26375 18.0401 4.00035 19.1425 4.92893 20.0711C5.11647 20.2586 5.37082 20.364 5.63604 20.364H18.364C18.6292 20.364 18.8835 20.2586 19.0711 20.0711C19.9997 19.1425 20.7362 18.0401 21.2388 16.8268C21.7413 15.6136 22 14.3132 22 13C22 10.3478 20.9464 7.8043 19.0711 5.92893C17.1957 4.05357 14.6522 3 12 3ZM6.34315 7.34315C7.84344 5.84285 9.87827 5 12 5C14.1217 5 16.1566 5.84285 17.6569 7.34315C19.1571 8.84344 20 10.8783 20 13C20 14.0506 19.7931 15.0909 19.391 16.0615C19.0406 16.9075 18.5479 17.6861 17.9353 18.364H6.06469C5.45205 17.6861 4.95938 16.9075 4.60896 16.0615C4.20693 15.0909 4 14.0506 4 13C4 10.8783 4.84285 8.84344 6.34315 7.34315Z"
                />
            </svg>
        )
    },
})

export const UserIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                {/* User Head */}
                <circle cx="12" cy="8" r="4" />

                {/* User Body */}
                <path
                    d="M4 20C4 16 8 14 12 14C16 14 20 16 20 20"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                />
            </svg>
        )
    },
})


export const AnnouncementIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                {/* Megaphone Shape */}
                <path
                    d="M3 8h8l5 4-5 4H3V8z"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                {/* Sound Waves */}
                <path
                    d="M18 8a3 3 0 0 1 0 8"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                />
                <path
                    d="M20 6a5 5 0 0 1 0 12"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                />
            </svg>
        )
    },
})

export const CardIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                {/* Card Outline */}
                <rect
                    x="2"
                    y="4"
                    width="20"
                    height="16"
                    rx="2"
                    ry="2"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                {/* Card Stripe */}
                <line
                    x1="2"
                    y1="10"
                    x2="22"
                    y2="10"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

export const DeviceIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                {/* Monitor Screen */}
                <rect
                    x="3"
                    y="4"
                    width="18"
                    height="12"
                    rx="2"
                    ry="2"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                />
                {/* Monitor Stand */}
                <line
                    x1="12"
                    y1="16"
                    x2="12"
                    y2="20"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <line
                    x1="8"
                    y1="20"
                    x2="16"
                    y2="20"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>
        )
    },
})

export const ArrowsInnerIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                stroke="currentColor"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M4 4L9 9M9 9V5M9 9H5M20 4L15 9M15 9V5M15 9H19M4 20L9 15M9 15H5M9 15V19M20 20L15 15M15 15H19M15 15V19"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

export const EmptyCircleIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M20.3149 15.4442C20.7672 14.3522 21 13.1819 21 12C21 9.61305 20.0518 7.32387 18.364 5.63604C16.6761 3.94821 14.3869 3 12 3C9.61305 3 7.32387 3.94821 5.63604 5.63604C3.94821 7.32387 3 9.61305 3 12C3 13.1819 3.23279 14.3522 3.68508 15.4442C4.13738 16.5361 4.80031 17.5282 5.63604 18.364C6.47177 19.1997 7.46392 19.8626 8.55585 20.3149C9.64778 20.7672 10.8181 21 12 21C13.1819 21 14.3522 20.7672 15.4442 20.3149C16.5361 19.8626 17.5282 19.1997 18.364 18.364C19.1997 17.5282 19.8626 16.5361 20.3149 15.4442Z"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

export const EntryIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                {/* Door Outline */}
                <rect
                    x="3"
                    y="3"
                    width="18"
                    height="18"
                    rx="2"
                    ry="2"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                {/* Entry Arrow */}
                <polyline
                    points="10 8 15 12 10 16"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

export const UnauthorizedIcon = defineComponent({
    setup() {
        return () => (
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                xmlns="http://www.w3.org/2000/svg"
            >
                {/* Lock Body */}
                <rect
                    x="6"
                    y="11"
                    width="12"
                    height="10"
                    rx="2"
                    ry="2"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                {/* Lock Shackle */}
                <path
                    d="M16 11V7a4 4 0 0 0-8 0v4"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                {/* Diagonal Slash */}
                <line
                    x1="3"
                    y1="3"
                    x2="21"
                    y2="21"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        )
    },
})

