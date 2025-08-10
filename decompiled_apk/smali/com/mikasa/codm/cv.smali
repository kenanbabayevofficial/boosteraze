.class Lcom/mikasa/codm/cv;
.super Ljava/lang/Object;

# interfaces
.implements Landroid/widget/SeekBar$OnSeekBarChangeListener;


# static fields
.field private static final short:[S


# instance fields
.field private final a:Lcom/mikasa/codm/Menu;

.field private final b:I

.field private final c:Ljava/lang/String;

.field private final d:I

.field private final e:Landroid/widget/TextView;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0x11

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/cv;->short:[S

    return-void

    :array_0
    .array-data 2
        0x368s
        0x372s
        0x36es
        0x334s
        0x33ds
        0x33cs
        0x326s
        0x372s
        0x331s
        0x33ds
        0x33es
        0x33ds
        0x320s
        0x36fs
        0x375s
        0x7f4s
        0x7eds
    .end array-data
.end method

.method native constructor <init>(Lcom/mikasa/codm/Menu;ILjava/lang/String;ILandroid/widget/TextView;)V
.end method

.method public static native ۣ۟ۢۤۦ(Ljava/lang/Object;)Ljava/lang/String;
.end method

.method public static native ۠ۨۧۧ(Ljava/lang/Object;)Ljava/lang/String;
.end method

.method public static native ۣۢۢۦ()[S
.end method

.method public static native ۢۦۥۣ(Ljava/lang/Object;)Lcom/mikasa/codm/Menu;
.end method

.method public static native ۥۡۥۦ(Ljava/lang/Object;)Landroid/widget/TextView;
.end method

.method public static native ۦۧۡۥ(Ljava/lang/Object;)I
.end method

.method public static native ۧۡۥ۠(Ljava/lang/Object;)I
.end method


# virtual methods
.method public native onProgressChanged(Landroid/widget/SeekBar;IZ)V
.end method

.method public native onStartTrackingTouch(Landroid/widget/SeekBar;)V
.end method

.method public native onStopTrackingTouch(Landroid/widget/SeekBar;)V
.end method
